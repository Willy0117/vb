<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Imagick;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

use App\Models\Member;
use App\Models\Status;
use App\Models\Progress;
use App\Models\OrganizationDocument;

use App\Http\Resources\MemberResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Helpers\DateHelper;
use App\Models\OperationLog;

class MemberController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $query = $this->buildMemberQuery($request);

        // =====================
        // ページング + 整形
        // =====================

        $perPage = (int) $request->input('per_page', 20);

        $statuses = Status::select('id', 'name')
            ->orderBy('id')
            ->get();

        $paginated = $query
            ->with(['organization.documents', 'status', 'progress'])
            ->paginate($perPage)
            ->withQueryString();

        // data 部分だけ加工（日本語ラベルなど）
        $members = $paginated->through(function ($member) {
            
            $statusId = $member->status_id;

            $date = match ($statusId) {
                2 => $member->joined_at,
                3 => $member->withdrawn_at,
                4 => $member->canceled_at,
                default => $member->created_at,
            };

            return [
                'id' => $member->id,
                'type' =>$member->type_label,  // 法人/個人事業主
                'agent' => $member->agent_label, // 代理人申請/本人申請
                'name' => $member->full_name,
                'status_id' => $member->status_id,
                'status' => $member->status ? [
                    'id' => $member->status->id,
                    'name' => $member->status->name, // ← ここで日本語に変換することも可能
                ] : null,
                'progress_id' => $member->status_id,
                'progress' => $member->progress ? [
                    'id' => $member->progress->id,
                    'name' => $member->progress->name, // ← ここで日本語に変換することも可能
                ] : null,
                'organization' => [
                    'name' => $member->organization?->full_name,
                ],
                'address' => $member->organization?->full_address,
                'documents' => $member->organization?->documents?->map(fn($doc) => [
                    'type' => $doc->type,
                    'path' => $doc->file_path ? Storage::url($doc->file_path) : null,
                    'thumbnail_path' => $doc->thumbnail_path ? Storage::url($doc->thumbnail_path) : null,
                ]),
                'created_at' => $member->created_at,
                'display_date' => $date ? DateHelper::withWareki($date) : null,
            ];
        });



        return Inertia::render('Admin/Members/Index', [
            'members' => $members,
            'filters' => [
                'status_id'    => $request->status_id ?? null,
                'per_page'     => $request->per_page ?? 20,
                'field'        => $request->field ?? '',
                'keyword'      => $request->keyword ?? '',
                'sort_by'      => $request->sort_by ?? 'created_at',  // ← 初期値
                'sort_dir'     => $request->sort_dir ?? 'desc',       // ← 初期値
            ],
        ]);
    }

    // 作成画面
    public function create()
    {
        return Inertia::render('Admin/Members/Create', [
            'member' => null
        ]);
    }

    // 保存
    public function store(Request $request)
    {
        $validated = $request->validate([
        ]);

        Member::create($validated);

        return redirect()->route('admin.members.index')
            ->with('success', __('member_created'));
    }

    public function show(Request $request, Member $member)
    {
        $member->load([
            'status',
            'progress',
            'region',
            'organizations',
            'organizations.documents', // documents はここで取得するだけ
            'applicationOrganization',
            'updatedByUser',
            'statusHistories.user',
            'progressHistories.user',
        ]);
        // persistQuery() 用に現在のクエリを保持
        $queryParams = $request->only([
            'company_name',
            'name',
            'tel',
            'per_page',
            'sort_by',
            'sort_dir',
            'page',
        ]);
        // 書類は type ごとに全部取得
        $documents = $member->organizations
            ->flatMap(fn ($org) => $org->documents)
            ->map(fn ($doc) => [
                'type'           => $doc->type,
                'path'           => $doc->file_path ? Storage::url($doc->file_path) : null,
                'thumbnail_path' => $doc->thumbnail_path ? Storage::url($doc->thumbnail_path) : null,
            ]);

        OperationLog::create([
            'user_id' => auth()->id(),
            'action' => 'member_view',
            'message' => '会員参照',
            'data' => [
                'member_id' => $member->id ?? null,
                'filters' => request()->all(),
            ],
        ]);

        $latestStatusHistory = $member->statusHistories->sortByDesc('created_at')->first();
        $latestProgressHistory = $member->progressHistories->sortByDesc('created_at')->first();        

        return Inertia::render('Admin/Members/Show', [
            'member' => [
                'id' => $member->id,
                // 申請者
                'first_name' => $member->first_name,
                'last_name'  => $member->last_name,
                'name'       => $member->full_name,
                'number'     => $member->number,
                'aplus_customer_no' => $member->aplus_customer_no,
                'jac_certification_no' => $member->jac_certification_no,
                'joined_at'     => $member->joined_at
                        ? DateHelper::withWareki($member->joined_at) : null,
                'withdrawn_at'  => $member->withdrawn_at
                        ? DateHelper::withWareki($member->withdrawn_at) : null,
                'canceled_at'   => $member->canceled_at
                        ? DateHelper::withWareki($member->canceled_at) : null,
                'region'        => $member->region->name ?? null,
                // ステータス
                'status'   => $member->status,
                'progress' => $member->progress,
                'updated_by_user' => $member->updatedByUser
                    ? [
                        'id' => $member->updatedByUser->id,
                        'name' => $member->updatedByUser->name,
                    ]
                    : null,
                'updated_at' => $member->updated_at,
                'status_meta' => $latestStatusHistory ? [
                    'updated_at' => $latestStatusHistory->created_at,
                    'user_name'  => $latestStatusHistory->user->name ?? null,
                ] : null,

                'progress_meta' => $latestProgressHistory ? [
                    'updated_at' => $latestProgressHistory->created_at,
                    'user_name'  => $latestProgressHistory->user->name ?? null,
                ] : null,
                // organization（typeごとに整理）
                'organizations' => $member->organizations->map(fn ($o) => [
                    'id'           => $o->id,
                    'type'         => $o->type,
                    'name'         => $o->full_name,
                    'postal_code'  => $o->postal_code,
                    'address'      => $o->full_address,
                    'tel'          => $o->tel,
                    'fax'          => $o->fax,
                    'mobile'       => $o->mobile,
                    'email'        => $o->email,
                    'contact_name' => $o->contact_name,
                ]),
                // organization（typeごとに整理）
                'applications' => $member->applicationorganizations->map(fn ($o) => [
                    'id'           => $o->id,
                    'type'         => $o->type,
                    'name'         => $o->full_name,
                    'postal_code'  => $o->postal_code,
                    'address'      => $o->full_address,
                    'tel'          => $o->tel,
                    'fax'          => $o->fax,
                    'mobile'       => $o->mobile,
                    'email'        => $o->email,
                    'contact_name' => $o->contact_name,
                ]),
                // 書類は独立
                'documents' => $documents,

                'created_at' => $member->created_at,
            ],
            // 検索条件をそのまま渡す
            'filters' => $request->only([
                'company_name', 'name', 'tel', 'per_page', 'sort_by', 'sort_dir', 'page','status_id',
            ]),            
        ]);
    }

    // 編集画面
    public function edit(Member $member, Request $request)
    {
        $member->load([
            'status',
            'progress',
            'organizations', // 複数
            'bankAccount',
            'invoice',
        ]);

        $orgs = $member->organizations->keyBy('type');
        // 書類は type ごとに全部取得
        $documents = $member->organizations
            ->flatMap(fn ($org) => $org->documents)
            ->map(fn ($doc) => [
                'type'           => $doc->type,
                'path'           => $doc->file_path ? Storage::url($doc->file_path) : null,
                'thumbnail_path' => $doc->thumbnail_path ? Storage::url($doc->thumbnail_path) : null,
            ]);

        // typeごとに変数に直接代入
        $corpOrg = $orgs->get(1);

        $corp  = $corpOrg ? [
            'name'         => $corpOrg->name,
            'name_kana'    => $corpOrg->name_kana,
            'prefix'       => $corpOrg->name_prefix,
            'suffix'       => $corpOrg->name_suffix,
            'postal_code'  => $corpOrg->postal_code,
            'address1'     => $corpOrg->address1,
            'address2'     => $corpOrg->address2,
            'address3'     => $corpOrg->address3,
            'tel'          => $corpOrg->tel,
            'fax'          => $corpOrg->fax,
            'mobile'       => $corpOrg->mobile,
            'email'        => $corpOrg->email,
            'position'     => $corpOrg->position,
            'contact_name' => $corpOrg->contact_name,
            'note'         => $corpOrg->note,
        ] : null;
        // 郵送先
        $mailOrg = $orgs->get(2);

        $mail  = $mailOrg ? [
            'name'         => $mailOrg->name,
            'prefix'       => $mailOrg->name_prefix,
            'suffix'       => $mailOrg->name_suffix,
            'postal_code'  => $mailOrg->postal_code,
            'address1'     => $mailOrg->address1,
            'address2'     => $mailOrg->address2,
            'address3'     => $mailOrg->address3,
            'tel'          => $mailOrg->tel,
            'fax'          => $mailOrg->fax,
            'mobile'       => $mailOrg->mobile,
            'email'        => $mailOrg->email,
            'position'     => $mailOrg->position,
            'last_name'    => $mailOrg->last_name,
            'first_name'   => $mailOrg->first_name,
        ] : null;
        
        $agentOrg = $orgs->get(3);

        $agent = $agentOrg ? [
            'company_name'         => $agentOrg->name,
            'prefix'       => $agentOrg->prefix,
            'suffix'       => $agentOrg->suffix,
            'postal_code'  => $agentOrg->postal_code,
            'address1'     => $agentOrg->address1,
            'address2'     => $agentOrg->address2,
            'address3'     => $agentOrg->address3,
            'tel'          => $agentOrg->tel,
            'fax'          => $agentOrg->fax,
            'mobile'       => $agentOrg->mobile,
            'email'        => $agentOrg->email,
            'position'     => $agentOrg->position,
            'last_name'    => $agentOrg->last_name,
            'first_name'   => $agentOrg->first_name,
        ] : null;

        $regions = DB::table('regions')->select('id', 'name')->orderBy('sort_order')->get();
        // Inertia に渡す
        return Inertia::render('Admin/Members/Edit', [
            'form' => [
                'id'          => $member->id,
                'region_id'   => $member->region_id,
                'number'      => $member->number,
                'rep_last_name'   => $member->last_name,
                'rep_first_name'  => $member->first_name,
                'status_id'   => $member->status_id,
                'progress_id' => $member->progress_id,
                'is_agent'    => $member->agent,
                'type'        => $member->type ?? 'corporation',
                'company_kana'=> $corp['name_kana'],
                'rep_last_kana' => $member->last_name_kana,
                'rep_first_kana' => $member->first_name_kana,
                'joined_at'   => $member->joined_at,
                'withdrawn_at'=> $member->withdrawn_at,
                'company_type_prefix' => $corp['prefix'],
                'company_name'=> $corp['name'],
                'company_type_suffix' => $corp['suffix'],
                'corp'        => $corp,
                'mail'        => $mail,
                'agent'       => $agent,
                'bank_account'=> $member->bankAccount,                
                'invoice'     => $member->invoice ? $member->invoice : null,                
                // 書類は独立
                'documents'   => $documents,
            ],
            'regions' => $regions,
            'filters' => $request->only(['company_name', 'name', 'tel', 'per_page', 'sort_by', 'sort_dir','status_id']),
        ]);
    }


    public function update(Request $request, Member $member)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
        ]);

        $member->update($data);

        return redirect()
            ->route('admin.member.show', $member)
            ->with('success', '更新しました');
    }

    // 削除
    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('admin.members.index')
            ->with('success', __('member_deleted'));
    }

    // 複数削除
    public function bulkDelete(Request $request)
    {
        Member::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.members.index')
            ->with('success', __('selected_members_deleted'));
    }

    public function autocomplete(Request $request)
    {
        $search = $request->input('q');

        $members = Member::query()
            ->when($search, fn($q) => $q->where('company_name', 'like', "%{$search}%"))
            ->when($search, fn($q) => $q->where('representative', 'like', "%{$search}%"))
            ->orderBy('company_name', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'label' => "{$m->company_name} ({$m->representative})"
            ]);

        return response()->json($members);
    }
    // AdminMemberController.php
    public function editStatus(Member $member)
    {
        $allowedMap = [
            1 => [1,2,4],
            2 => [2,3],
            3 => [],
            4 => [],
        ];

        $allowedIds = $allowedMap[$member->status_id] ?? [];

        return response()->json([
            'member' => [
                'status_id' => $member->status_id,
            ],
            'statuses' => Status::select('id', 'name')
                ->whereIn('id', $allowedIds)
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function updateStatus(Request $request, Member $member)
    {
        $request->validate([
            'status_id' => ['required', 'exists:statuses,id'],
            'date'      => ['required', 'date'],
        ]);

        $statusId = (int) $request->status_id;
        $dt = \Carbon\Carbon::parse($request->date)->second(0);

        $data = [
            'status_id' => $statusId,
            'updated_by' => auth()->id(),
        ];

        switch ($statusId) {
            case 2: // 入会
                $data['joined_at']   = $dt;
                $data['canceled_at'] = null;
                $data['withdrawn_at'] = null;
                break;

            case 3: // 退会
                $data['withdrawn_at'] = $dt;
                break;

            case 4: // 申込キャンセル
                $data['canceled_at'] = $dt;
                break;

            case 1: // 申請中
            default:
                // 日時は触らない
                break;
        }

        $member->update($data);
        // JSONで更新済み member を返す
        return response()->json(['member' => $member->fresh()]);

    }

    
    public function editProgress(Member $member)
    {
        return response()->json([
            'member' => [
                'id' => $member->id,
                'progress_id' => $member->progress_id,
            ],
            'progresses' => Progress::select('id', 'name')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function updateProgress(Request $request, Member $member)
    {
        $request->validate([
            'progress_id' => ['required', 'exists:progresses,id'],            
        ]);

        $member->update([
            'progress_id' => $request->progress_id,
            'updated_by' => auth()->id(),
        ]);
        // JSONで更新済み member を返す
        return response()->json(['member' => $member->fresh()]);
    }

    public function uploadDocument(Request $request, Member $member)
    {
        $request->validate([
            'type_id' => 'required|integer|in:1,2,3,4',
            'document' => 'required|file|mimes:pdf|max:10240',
        ]);
        // member → organizations（法人）
        $organization = $member->organizations()
            ->where('type', 1)
            ->first();

        if (!$organization) {
            abort(404, '法人organizationが見つかりません');
        }

        $organizationId = $organization->id;

        // type_id によってアップロード先フォルダを振り分け
        $folderMap = [
            1 => 'members/history_certificates',
            2 => 'members/address_certificates',
            3 => 'members/bank_transfer_forms',
            4 => 'members/power_of_attorney',
        ];

        $folder = $folderMap[$request->type_id] ?? 'members/others';

        // PDFアップロード＋サムネイル生成
        [$filePath, $thumbPath] = $this->storePdfWithThumbnail(
            $request->file('document'),
            $folder
        );

        // DB保存（organization_documents）
        OrganizationDocument::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'type' => $request->type_id,
            ],
            [
                'file_path' => $filePath,
                'thumbnail_path' => $thumbPath,
                'verified_at' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'file_url' => Storage::url($filePath),
            'thumbnail_url' => $thumbPath ? Storage::url($thumbPath) : null,
        ]);
    }

    public function saveBasic(Request $request, Member $member)
    {
        $rules = [
            // ===== 基本情報 =====
            'type'          => 'required|string',
            'region_id'     => 'nullable|integer',
            'number'        => 'nullable|string',
            'company_kana'  => 'required|string',
            'rep_last_kana' => 'required|string',
            'rep_first_kana'=> 'required|string',
            'company_type_prefix' => 'required|string',
            'company_name'  => 'required|string',
            'company_type_suffix' => 'nullable|string',
            'rep_last_name' => 'required|string',
            'rep_first_name'=> 'required|string',
            'joined_at'     => 'nullable|date',
            'withdrawn_at'  => 'nullable|date',
            'aplus_customer_no' => 'nullable|string',
            'jac_certification_no' => 'nullable|string',
            'same_as_corp'  => 'boolean',
            'issued_at' => 'nullable|string',
            'paid_at' => 'nullable|string',
            'amount' => 'nullable|integer',
            // ===== 法人（corp）=====
            'corp' => 'required|array',
            'corp.type' => 'required|integer',
            'corp.postal_code' => 'required|string',
            'corp.address1' => 'required|string',
            'corp.address2' => 'required|string',
            'corp.address3' => 'nullable|string',
            'corp.tel'      => 'required|string',
            'corp.fax'      => 'nullable|string',
            'corp.mobile'   => 'nullable|string',
            'corp.email'     => 'nullable|string',
            'corp.position' => 'required|string',
            'corp.last_name'=> 'required|string',
            'corp.first_name' => 'required|string',
            'corp.note'     => 'nullable|string',


        ];

        $validated = $request->validate($rules);

        // 保存前の状態
        $memberBefore = $member->getOriginal();
        $invoiceBefore = $member->invoice?->toArray();
        $organizationBefore = $member->organizations()->where('type', 1)->first()?->toArray();

        $form = $validated;
        $corp = $validated['corp'];

        $member->update([
            'type'        => $form['type'],
            'region_id'   => $form['region_id'],
            'last_name'   => $form['rep_last_name'],
            'first_name'  => $form['rep_first_name'],
            'last_name_kana'   => $form['rep_last_kana'],
            'first_name_kana'  => $form['rep_first_kana'],
            'number'      => $form['number'] ?? null,
            'joined_at'   => $form['joined_at'] ?? null,
            'withdrawn_at'=> $form['withdrawn_at'] ?? null,
            'aplus_customer_no' => $form['aplus_customer_no'] ?? null,
            'jac_certification_no' => $form['jac_certification_no'] ?? null,
            'updated_by'  => auth()->id(),
        ]);

        $member->invoice()->updateOrCreate(
            [],
            [
                'issued_at' => $form['issued_at'] ?? null,
                'paid_at'   => $form['paid_at'] ?? null,
                'amount'    => $form['amount'] ?? 0,
            ]
        );

        $member->organizations()->updateOrCreate(
            ['type' => 1],
            [
                'name'        => $form['company_name'],
                'name_kana'   => $form['company_kana'],
                'name_prefix' => $form['company_type_prefix'],
                'name_suffix' => $form['company_type_suffix'],
                'postal_code' => $corp['postal_code'] ?? null,
                'address1'    => $corp['address1'] ?? null,
                'address2'    => $corp['address2'] ?? null,
                'address3'    => $corp['address3'] ?? null,
                'tel'         => $corp['tel'] ?? null,
                'fax'         => $corp['fax'] ?? null,
                'mobile'      => $corp['mobile'] ?? null,
                'email'       => $corp['email'] ?? null,
                'position'    => $corp['position'] ?? null,
                'last_name'   => $corp['last_name'] ?? null,
                'first_name'  => $corp['first_name'] ?? null,
                'note'        => $corp['note'] ?? null,
            ]
        );
        // 更新後の状態
        $memberAfter = $member->getChanges();
        $invoiceAfter = $member->invoice?->getChanges();
        $organizationAfter = $member->organizations()->where('type', 1)->first()?->getChanges();

        // ログ落とし
        OperationLog::create([
            'user_id' => auth()->id(),
            'action' => 'member_update_basic',
            'message' => 'Basic 部更新 (members + invoice + organization type=1)',
            'data' => [
                'member' => ['before' => $memberBefore, 'after' => $memberAfter],
                'invoice' => ['before' => $invoiceBefore, 'after' => $invoiceAfter],
                'organization' => ['before' => $organizationBefore, 'after' => $organizationAfter],
            ],
        ]);

        return back();


    }
    /*
        郵送先をupdate無ければ新規作成
        */
    public function saveMail(Request $request, Member $member)
    {
       $rules = [
            'company_kana' => 'required|string',
            'company_type_prefix' => 'required|string',
            'company_name' => 'required|string',
            'company_type_suffix' => 'nullable|string',
            // ===== 郵送先（mail）=====
            'mail' => 'required|array',
            'mail.type' => 'required|integer',
            'mail.postal_code' => 'nullable|string',
            'mail.address1' => 'nullable|string',
            'mail.address2' => 'nullable|string',
            'mail.address3' => 'nullable|string',
            'mail.tel' => 'nullable|string',
            'mail.fax' => 'nullable|string',
            'mail.mobile' => 'nullable|string',
            'mail.email' => 'nullable|email',
            'mail.position' => 'nullable|string',
            'mail.last_name' => 'nullable|string',
            'mail.first_name' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        // 保存前の状態
        $organizationBefore = $member->organizations()->where('type', 2)->first()?->toArray();

        $form = $validated;
        $mail = $validated['mail'];

        $member->organizations()->updateOrCreate(
            ['type' => 2],
            [
                'name'        => $form['company_name'],
                'name_kana'   => $form['company_kana'],
                'name_prefix' => $form['company_type_prefix'],
                'name_suffix' => $form['company_type_suffix'],
                'postal_code' => $mail['postal_code'] ?? null,
                'address1'    => $mail['address1'] ?? null,
                'address2'    => $mail['address2'] ?? null,
                'address3'    => $mail['address3'] ?? null,
                'tel'         => $mail['tel'] ?? null,
                'fax'         => $mail['fax'] ?? null,
                'mobile'      => $mail['mobile'] ?? null,
                'email'       => $mail['email'] ?? null,
                'position'    => $mail['position'] ?? null,
                'last_name'   => $mail['last_name'] ?? null,
                'first_name'  => $mail['first_name'] ?? null,
            ]
        );

        $organizationAfter = $member->organizations()->where('type', 2)->first()?->getChanges();

        // ログ落とし
        OperationLog::create([
            'user_id' => auth()->id(),
            'action' => 'member_update_basic',
            'message' => '郵送更新 (organization type=2)',
            'data' => [
                'organization' => ['before' => $organizationBefore, 'after' => $organizationAfter],
            ],
        ]);


        return back();
    }
    /*
        代理店をupdate無ければ新規作成
    */
    public function saveAgent(Request $request, Member $member)
    {
       $rules = [
            'agent.company_name' => 'required|string',
            'agent.email' => 'required|email',
            'agent' => 'required|array',
            'agent.type' => 'required|integer',
            'agent.company_name' => 'required|string',
            'agent.postal_code' => 'required|string',
            'agent.address1' => 'required|string',
            'agent.address2' => 'nullable|string',
            'agent.address3' => 'nullable|string',
            'agent.tel' => 'required|string',
            'agent.fax' => 'nullable|string',
            'agent.mobile' => 'nullable|string',
            'agent.position' => 'required|string',
            'agent.last_name' => 'required|string',
            'agent.first_name' => 'required|string',            
        ];

        $validated = $request->validate($rules);
        // 保存前の状態
        $organizationBefore = $member->organizations()->where('type', 3)->first()?->toArray();

        $form = $validated;
        $agent = $validated['agent'];

        $member->organizations()->updateOrCreate(
            ['type' => 3],
            [
                'name'        => $agent['company_name'],
                'postal_code' => $agent['postal_code'] ?? null,
                'address1'    => $agent['address1'] ?? null,
                'address2'    => $agent['address2'] ?? null,
                'address3'    => $agent['address3'] ?? null,
                'tel'         => $agent['tel'] ?? null,
                'fax'         => $agent['fax'] ?? null,
                'mobile'      => $agent['mobile'] ?? null,
                'email'       => $agent['email'] ?? null,
                'position'    => $agent['position'] ?? null,
                'last_name'   => $agent['last_name'] ?? null,
                'first_name'  => $agent['first_name'] ?? null,
            ]
        );

        $organizationAfter = $member->organizations()->where('type', 3)->first()?->getChanges();

        // ログ落とし
        OperationLog::create([
            'user_id' => auth()->id(),
            'action' => 'member_update_basic',
            'message' => '代理店更新 (organization type=3)',
            'data' => [
                'organization' => ['before' => $organizationBefore, 'after' => $organizationAfter],
            ],
        ]);

        return back();
    }

    public function saveBank(Request $request, Member $member)
    {
        $rules = [
            'bank_type' => 'required|integer',
            'bank_name' => 'required|string',
            'bank_code' => 'required|string',
            'branch_code' => 'required|string',
            'account_type' => 'required|string',
            'account_no' => 'required|string',
            'account_kana' => 'required|string',
            'account_name' => 'required|string',
        ];
        if ($request->input('bank_code') !== '9900') {
            $rules['branch_name'] = 'required|string';
        }

        $bank = $request->validate($rules);

        // bank_accounts
        $member->bankAccount()->updateOrCreate(
            [],
            [
                'bank_type' => $bank['bank_type'],
                'bank_name' => $bank['bank_name'],
                'bank_code' => $bank['bank_code'] ?? null,
                'branch_name' => $bank['branch_name'] ?? null,
                'branch_code' => $bank['branch_code'] ?? null,
                'account_type' => $bank['account_type'],
                'account_no' => $bank['account_no'],
                'account_kana' => $bank['account_kana'],
                'account_name' => $bank['account_name'],
            ]
        );

        return back();
    }


    // pdf upload＋thumbnail(png)作成関数    
    private function storePdfWithThumbnail(
        ?UploadedFile $file,
        string $baseDir
    ): array {
/* debug用
logger()->error('BASE DIR DEBUG', [
    'file' => $file,
    'baseDir' => $baseDir,
    'length' => strlen($baseDir),
]);
*/
        if (!$file) {
            return [null, null];
        }

        if (!$file->isValid()) {
            throw new \RuntimeException('Upload is not valid');
        }

        // PDF 保存（public）
        $pdfRelativePath = $file->store($baseDir, 'public');

        if (!$pdfRelativePath) {
            throw new \RuntimeException('PDF store failed');
        }

        $pdfFullPath = storage_path('app/public/' . $pdfRelativePath);

        if (!is_file($pdfFullPath)) {
            throw new \RuntimeException('PDF not found: ' . $pdfFullPath);
        }

        // thumbnail 保存先
        $thumbDir = $baseDir . '/thumbnails';
        if (!Storage::disk('public')->exists($thumbDir)) {
            Storage::disk('public')->makeDirectory($thumbDir);
        }

        $thumbnailRelativePath =
            $thumbDir . '/' . pathinfo($pdfRelativePath, PATHINFO_FILENAME) . '.png';
        $thumbnailFullPath = storage_path('app/public/' . $thumbnailRelativePath);

        // thumbnail 生成
        $imagick = new \Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage($pdfFullPath . '[0]');
        $imagick->setImageFormat('png');
        $imagick->writeImage($thumbnailFullPath);
        $imagick->clear();
        $imagick->destroy();

        return [$pdfRelativePath, $thumbnailRelativePath];
    }


    public function csv(Request $request): StreamedResponse
    {
        // 一覧と同じ Query を組み立てる
        $query = $this->buildMemberQuery($request);

//        $members = Member::with(['corpOrg', 'mailOrg', 'agentOrg', 'bankAccount', 'region','invoice'])->get();
        $members = $query
            ->with([
                'corpOrg',
                'mailOrg',
                'agentOrg',
                'bankAccount',
                'region',
                'invoice'
            ])
            ->get();

        $response = new StreamedResponse(function () use ($members) {
            $handle = fopen('php://output', 'w');
            // Excel文字化け対策（BOM）
            fwrite($handle, "\xEF\xBB\xBF");

            // =====================
            // ヘッダ行
            // =====================
            fputcsv($handle, [
                    '',
                    '外国人受入支援番号',
                    '会社名',
                    '代表者',
                    '代表者肩書・役職',
                    '郵便番号',
                    '所在地１（都道府県・市区町村名・番地）',
                    '所在地２（建物ビル名）',
                    '電話',
                    'FAX',
                    '携帯番号',
                    'E-mail',
                    '加入年月日',
                    '退会年月日',
                    '外国人会費請求日',
                    '外国人会費入金日',
                    '外国人会費入金額',
                    '備考',
                    '',
                    '顧客地域',
                    '会社名フリガナ',
                    '代表者フリガナ',
                    '申込担当者',
                    '指定郵送先郵便番号',
                    '指定郵送先住所',
                    '指定郵送先電話番号',
                    '指定郵送先FAX',
                    '指定郵送先携帯番号',
                    '指定郵送先担当者',
                    '代理申込名前',
                    '代理申込電話番号',
                    '代理申込住所',
                    '代理申込担当者',
                    '代理申込電話',
                    '代理申込FAX',
                    '代理申込メール',
                    '口座振替銀行名',
                    '口座振替銀行コード',
                    '口座振替支店名',
                    '口座振替支店コード',
                    '口座振替口座種別',
                    '口座振替口座番号',
                    '口座振替口座名義人',
                    '口座振替口座名義フリガナ',
                    'ｱﾌﾟﾗｽ口振依頼書顧客番号',
                    'JAC認定番号',
                ]);

            // =====================
            // データ行
            // =====================

            foreach ($members as $member) {

                $corp  = $member->corpOrg;
                $mail  = $member->mailOrg;
                $agent = $member->agentOrg;

                // ===== 住所結合 =====
                $corpAddress = trim(
                    ($corp->address1 ?? '') .
                    ($corp->address2 ?? '') 
                );

                $mailAddress = trim(
                    ($mail->address1 ?? '') .
                    ($mail->address2 ?? '') .
                    ($mail->address3 ?? '')
                );

                $agentAddress = trim(
                    ($agent->address1 ?? '') .
                    ($agent->address2 ?? '') .
                    ($agent->address3 ?? '')
                );

                // ===== 氏名結合 =====
                $corpRepresentative = trim(
                    ($corp->last_name ?? '') . ' ' .
                    ($corp->first_name ?? '')
                );

                $agentName = trim(
                    ($agent->last_name ?? '') . ' ' .
                    ($agent->first_name ?? '')
                );

                // ===== 会費（最新1件想定）=====
                $invoice = $member->invoice;

                fputcsv($handle, [

                    '',

                    // 外国人受入支援番号
                    $member->number,

                    // 会社名
                    $corp->name ?? '',

                    // 代表者
                    $corpRepresentative,

                    // 代表者肩書
                    $corp->position ?? '',

                    // 郵便番号
                    $corp->postal_code ?? '',

                    // 所在地1（統合）
                    $corpAddress,

                    $corp->address3 ?? '',

                    // 電話
                    $corp->tel ?? '',

                    // FAX
                    $corp->fax ?? '',

                    // 携帯番号
                    $corp->mobile ?? '',

                    // E-mail
                    $corp->email ?? '',

                    // 加入年月日
                    optional($member->joined_at)?->format('Y-m-d'),

                    // 退会年月日
                    optional($member->withdrawn_at)?->format('Y-m-d'),

                    // 外国人会費請求日
                    optional($invoice)->billing_date?->format('Y-m-d'),

                    // 外国人会費入金日
                    optional($invoice)->paid_at?->format('Y-m-d'),

                    // 外国人会費入金額
                    $invoice->amount ?? '',
                    // 備考
                    $corp->note ?? '',

                    '',
                    // 顧客地域
                    optional($member->region)->name ?? '',
                    // 会社名フリガナ
                    $corp->name_kana ?? '',
                    // 代表者フリガナ（member側）
                    trim(($member->last_name_kana ?? '') . ' ' . ($member->first_name_kana ?? '')),
                    // 申込担当者
                    $corp->Representative ?? '',
                    // ===== 指定郵送先（type=2）=====
                    $mail->postal_code ?? '',
                    $mailAddress,
                    $mail->tel ?? '',
                    $mail->fax ?? '',
                    $mail->mobile ?? '',
                    trim(($mail->last_name ?? '') . ' ' . ($mail->first_name ?? '')),

                    // ===== 代理申込（type=3）=====
                    $agentName,
                    $agent->tel ?? '',
                    $agentAddress,
                    trim(($agent->last_name ?? '') . ' ' . ($agent->first_name ?? '')),
                    $agent->tel ?? '',
                    $agent->fax ?? '',
                    $agent->email ?? '',

                    // ===== 口座 =====
                    optional($member->bankAccount)->bank_name ?? '',
                    optional($member->bankAccount)->bank_code ?? '',
                    optional($member->bankAccount)->branch_name ?? '',
                    optional($member->bankAccount)->branch_code ?? '',
                    optional($member->bankAccount)->account_type ?? '',
                    optional($member->bankAccount)->account_no ?? '',
                    optional($member->bankAccount)->account_name ?? '',
                    optional($member->bankAccount)->account_kana ?? '',

                    // 追加
                    $member->aplus_customer_no ?? '',
                    $member->jac_certification_no ?? '',
                ]);
            }

            fclose($handle);
        });

        $filename = 'members_' . now()->format('Ymd_His') . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition',
            "attachment; filename=\"{$filename}\""
        );

        return $response;
    } 

    public function export()
    {

        $handle = fopen('php://output', 'r+');

        // Excel文字化け対策（BOM）
        fwrite($handle, "\xEF\xBB\xBF");

        // ヘッダー
        fputcsv($handle, [
            '外国人受入支援番号',
            '会社名',
            '代表者',
            '代表者肩書・役職',
            '郵便番号',
            '所在地１（都道府県・市区町村名・番地）',
            '所在地２（建物ビル名）',
            '電話',
            'FAX',
            '携帯番号',
            'E-mail',
            '加入年月日',
            '退会年月日',
            '外国人会費請求日',
            '外国人会費入金日',
            '外国人会費入金額',
            '備考',
            '顧客地域',
            '会社名フリガナ',
            '代表者フリガナ',
            '申込担当者',
            '指定郵送先郵便番号',
            '指定郵送先住所',
            '指定郵送先電話番号',
            '指定郵送先FAX',
            '指定郵送先携帯番号',
            '指定郵送先担当者',
            '代理申込名前',
            '代理申込電話番号',
            '代理申込住所',
            '代理申込担当者',
            '代理申込電話',
            '代理申込FAX',
            '代理申込メール',
            '口座振替銀行名',
            '口座振替銀行コード',
            '口座振替支店名',
            '口座振替支店コード',
            '口座振替口座種別',
            '口座振替口座番号',
            '口座振替口座名義人',
            '口座振替口座名義フリガナ',
            'ｱﾌﾟﾗｽ口振依頼書顧客番号',
            'JAC認定番号',
        ]);

        $members = Member::with(['corp', 'mail', 'agent', 'bankAccount', 'region','invoice'])->get();

        foreach ($members as $member) {

            $corp  = $member->corp;
            $mail  = $member->mail;
            $agent = $member->agent;

            // ===== 住所結合 =====
            $corpAddress = trim(
                ($corp->address1 ?? '') .
                ($corp->address2 ?? '') .
                ($corp->address3 ?? '')
            );

            $mailAddress = trim(
                ($mail->address1 ?? '') .
                ($mail->address2 ?? '') .
                ($mail->address3 ?? '')
            );

            $agentAddress = trim(
                ($agent->address1 ?? '') .
                ($agent->address2 ?? '') .
                ($agent->address3 ?? '')
            );

            // ===== 氏名結合 =====
            $corpRepresentative = trim(
                ($corp->last_name ?? '') . ' ' .
                ($corp->first_name ?? '')
            );

            $agentName = trim(
                ($agent->last_name ?? '') . ' ' .
                ($agent->first_name ?? '')
            );

            // ===== 会費（最新1件想定）=====
            $invoice = $member->invoice;

            fputcsv($handle, [
                // 外国人受入支援番号
                $member->number,
                // 会社名
                $corp->name ?? '',
                // 代表者
                $corpRepresentative,
                // 代表者肩書
                $corp->position ?? '',
                // 郵便番号
                $corp->postal_code ?? '',
                // 所在地1（統合）
                $corpAddress,
                // 所在地2（今回は分けないので空）
                '',
                // 電話
                $corp->tel ?? '',

                // FAX
                $corp->fax ?? '',

                // 携帯番号
                $corp->mobile ?? '',

                // E-mail
                $corp->email ?? '',

                // 加入年月日
                optional($member->joined_at)?->format('Y-m-d'),

                // 退会年月日
                optional($member->withdrawn_at)?->format('Y-m-d'),

                // 外国人会費請求日
                optional($invoice)->issued_at?->format('Y-m-d'),

                // 外国人会費入金日
                optional($invoice)->paid_at?->format('Y-m-d'),

                // 外国人会費入金額
                $invoice->amount ?? '',

                // 備考（organizations.noteではなくmemberに無いので空）
                '',

                // 顧客地域
                optional($member->region)->name ?? '',

                // 会社名フリガナ
                $corp->name_kana ?? '',

                // 代表者フリガナ（member側）
                trim(($member->last_name_kana ?? '') . ' ' . ($member->first_name_kana ?? '')),

                // 申込担当者
                '',

                // ===== 指定郵送先（type=2）=====
                $mail->postal_code ?? '',
                $mailAddress,
                $mail->tel ?? '',
                $mail->fax ?? '',
                $mail->mobile ?? '',
                trim(($mail->last_name ?? '') . ' ' . ($mail->first_name ?? '')),

                // ===== 代理申込（type=3）=====
                $agentName,
                $agent->tel ?? '',
                $agentAddress,
                trim(($agent->last_name ?? '') . ' ' . ($agent->first_name ?? '')),
                $agent->tel ?? '',
                $agent->fax ?? '',
                $agent->email ?? '',

                // ===== 口座 =====
                optional($member->bankAccount)->bank_name ?? '',
                optional($member->bankAccount)->bank_code ?? '',
                optional($member->bankAccount)->branch_name ?? '',
                optional($member->bankAccount)->branch_code ?? '',
                optional($member->bankAccount)->account_type ?? '',
                optional($member->bankAccount)->account_no ?? '',
                optional($member->bankAccount)->account_name ?? '',
                optional($member->bankAccount)->account_kana ?? '',

                // 追加
                $member->aplus_customer_no ?? '',
                $member->jac_certification_no ?? '',
            ]);
        }
        rewind($handle);

        return response()->streamDownload(function () use ($handle) {
            fpassthru($handle);
        }, 'members.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function checkNumber(Request $request)
    {
        $number = $request->input('number');
        $exists = Member::where('number', $number)->exists();
        return response()->json(['available' => !$exists]);
    }


    private function buildMemberQuery(Request $request)
    {
       $query = Member::query()
            ->with([
                'status',
                'progress',
                'organization' => fn ($q) => $q->where('type', 1),
                'organization.documents',// => fn ($q) => $q->where('type', 1), // 履歴事項全部証明書
            ])
            ->when(request('status_id'), function ($q, $status_id) {
                $q->where('status_id', $status_id);
            });
        // =====================
        // フィールド指定検索（NEW）
        // =====================

        $field = $request->input('field');
        $keyword = trim($request->input('keyword'));

        $memberFields = [
            'number',
            'aplus_customer_no',
            'jac_certification_no',
            'last_name',
            'first_name',
            'last_name_kana',
            'first_name_kana',
        ];

        $organizationFields = [
            'company_name',           // 実際は name
            'company_kana',           // name_kana
            'representative_name',    // last_name + first_name
            'representative_kana',    // last_name_kana + first_name_kana
            'tel',
            'note',
        ];

        if ($field && $keyword) {

            // 正規化
            $keyword = mb_convert_kana($keyword, 'as');

            // =====================
            // members側
            // =====================
            if (in_array($field, $memberFields)) {

                $query->where($field, 'like', "%{$keyword}%");

            }

            // =====================
            // organizations側
            // =====================
            elseif (in_array($field, $organizationFields)) {

                $query->whereHas('organization', function ($q) use ($field, $keyword) {

                    $q->where('type', 1);

                    if ($field === 'company_name') {
                        $q->where('name', 'like', "%{$keyword}%");
                    }

                    elseif ($field === 'company_kana') {
                        $q->where('name_kana', 'like', "%{$keyword}%");
                    }

                    elseif ($field === 'representative_name') {
                        $q->where(function ($qq) use ($keyword) {
                            $qq->where('last_name', 'like', "%{$keyword}%")
                            ->orWhere('first_name', 'like', "%{$keyword}%");
                        });
                    }

                    elseif ($field === 'representative_kana') {
                        $q->where(function ($qq) use ($keyword) {
                            $qq->where('last_name_kana', 'like', "%{$keyword}%")
                            ->orWhere('first_name_kana', 'like', "%{$keyword}%");
                        });
                    }

                    elseif ($field === 'tel') {
                        $q->where(function ($qq) use ($keyword) {
                            $qq->where('tel', 'like', "%{$keyword}%");
                        });
                    }

                    elseif ($field === 'note') {
                        $q->where(function ($qq) use ($keyword) {
                            $qq->where('note', 'like', "%{$keyword}%");
                        });
                    }
                });
            }
        }

        // =====================
        // ソート（membersのみ）
        // =====================

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = [
            'id',
            'status_id',
            'progress_id',
            'address',
            'company_name',
            'representative',            
            'created_at',
        ];

        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        /*
        |--------------------------------------------------------------------------
        | JOIN が必要なソート
        |--------------------------------------------------------------------------
        */
        if (in_array($sortBy, ['address', 'company_name'])) {

            $query
                ->leftJoin('organizations as org', function ($join) {
                    $join->on('org.member_id', '=', 'members.id')
                        ->where('org.type', 1); // 法人のみ
                })
                ->select('members.*');

            if ($sortBy === 'address') {

                $query->orderByRaw("
                    CONCAT(
                        IFNULL(org.address1, ''),
                        IFNULL(org.address2, ''),
                        IFNULL(org.address3, '')
                    ) {$sortDir}
                ");

            } elseif ($sortBy === 'company_name') {

               $query->orderByRaw("
                        CONCAT(
                            IFNULL(org.name_prefix, ''),
                            IFNULL(org.name, ''),
                            IFNULL(org.name_suffix, '')
                        ) {$sortDir}
                    ");
            }

        /*
        |--------------------------------------------------------------------------
        | member 名（full_name 相当）
        |--------------------------------------------------------------------------
        */
        } elseif ($sortBy === 'representative') {

            $query
                ->orderBy('members.last_name', $sortDir)
                ->orderBy('members.first_name', $sortDir);

        /*
        |--------------------------------------------------------------------------
        | members 単体で完結するソート
        |--------------------------------------------------------------------------
        */
        } else {

            $query->orderBy("members.{$sortBy}", $sortDir);
        }

        return $query;
    }

}
