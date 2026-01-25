<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\Member;
use App\Http\Resources\MemberResource;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $query = Member::query()
            ->with([
                'status',
                'progress',
                'organization',
                'organization.documents' => fn ($q) => $q->where('type', 1), // 履歴事項全部証明書
            ]);

        // =====================
        // 検索
        // =====================

        if ($companyName = $request->input('company_name')) {
            $query->whereHas('organization', function ($q) use ($companyName) {
                $q->where('name', 'like', "%{$companyName}%");
            });
        }

        if ($name = $request->input('name')) {
            $query->whereHas('organization.contacts', function ($q) use ($name) {
                $q->where(function ($qq) use ($name) {
                    $qq->where('last_name', 'like', "%{$name}%")
                       ->orWhere('first_name', 'like', "%{$name}%");
                });
            });
        }

        // =====================
        // ソート（membersのみ）
        // =====================

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        if (! in_array($sortBy, ['id', 'created_at'])) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortDir);

        // =====================
        // ページング + 整形
        // =====================

        $perPage = (int) $request->input('per_page', 20);

        $members = $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($member) {

                $org = $member->organization;
                $doc = $org?->documents?->first(); // type=1 は with 側で絞る前提

                return [
                    'id' => $member->id,
                    'type' => $member->type_label,
                    'agent' => $member->agent_label,
                    // ステータス
                    'status'   => $member->status,
                    'progress' => $member->progress,

                    // 法人
                    'organization' => [
                        'name' => $org?->full_name,
                    ],

                    // 申請者（member）
                    'name' => $member->full_name,

                    // 連絡先（organization に集約）
                    'tel' => $org?->tel,
                    'address' => $org?->full_address,

                    // 履歴事項全部証明書
                    'history_certificate' => $doc ? [
                        'path' => $doc->path
                            ? Storage::url($doc->path)
                            : null,
                        'thumbnail_path' => $doc->thumbnail_path
                            ? Storage::url($doc->thumbnail_path)
                            : null,
                    ] : null,

                    'mail_address_certificate' => $doc ? [
                        'path' => $doc->path
                            ? Storage::url($doc->path)
                            : null,
                        'thumbnail_path' => $doc->thumbnail_path
                            ? Storage::url($doc->thumbnail_path)
                            : null,
                    ] : null,

                    'created_at' => $member->created_at,
                ];
            });

        return Inertia::render('Admin/Members/Index', [
            'members' => $members,
            'filters' => $request->only([
                'company_name',
                'name',
                'per_page',
                'sort_by',
                'sort_dir',
            ]),
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
            'organizations',
            'organizations.documents', // documents はここで取得するだけ
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
                'path'           => $doc->path ? Storage::url($doc->path) : null,
                'thumbnail_path' => $doc->thumbnail_path ? Storage::url($doc->thumbnail_path) : null,
            ]);

        return Inertia::render('Admin/Members/Show', [
            'member' => [
                'id' => $member->id,

                // 申請者
                'first_name' => $member->first_name,
                'last_name'  => $member->last_name,
                'name'       => $member->full_name,

                // ステータス
                'status'   => $member->status,
                'progress' => $member->progress,

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

                // 書類は独立
                'documents' => $documents,

                'created_at' => $member->created_at,
            ],
            // 検索条件をそのまま渡す
            'filters' => $request->only([
                'company_name', 'name', 'tel', 'per_page', 'sort_by', 'sort_dir', 'page'
            ]),            
        ]);
    }

    // 編集画面
    public function edit(Member $member, Request $request)
    {
        $member->load([
            'status',
            'progress',
            'organization',
        ]);

        $org = $member->organization;

        return Inertia::render('Admin/Members/Edit', [
            'member' => [
                'id'         => $member->id,
                'last_name'  => $member->last_name,
                'first_name' => $member->first_name,
                'status_id'  => $member->status_id,
                'progress_id'=> $member->progress_id,
                // 法人情報も分割して渡す
                'organization' => $org ? [
                    'name'         => $org->name,
                    'prefix'       => $org->prefix,
                    'suffix'       => $org->suffix,
                    'postal_code'  => $org->postal_code,
                    'address1'     => $org->address1,
                    'address2'     => $org->address2,
                    'address3'     => $org->address3,
                    'tel'          => $org->tel,
                    'fax'          => $org->fax,
                    'mobile'       => $org->mobile,
                    'email'        => $org->email,
                    'contact_name' => $org->contact_name,
                ] : null,
            ],
            'filters' => $request->only(['company_name', 'name', 'tel', 'per_page', 'sort_by', 'sort_dir']),
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

}
