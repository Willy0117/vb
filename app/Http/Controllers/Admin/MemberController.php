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
                'organization.contacts' => fn ($q) => $q->where('type', 1), // 代表者
                'organization.addresses' => fn ($q) => $q->where('type', 1), // 登記住所
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

                $document = $member->organization
                    ?->documents
                    ?->first();

                $representative = $member->organization
                    ?->contacts
                    ?->first();
                $contact = $member->organization?->contacts?->first();
                $address = $member->organization?->addresses?->first();

                return [
                    'id' => $member->id,

                    // ステータス
                    'status' => $member->status,
                    'progress' => $member->progress,

                    // 法人
                    'organization' => [
                        'name' => $member->organization?->full_name,
                    ],

                    // 代表者
                    'representative' => $representative?->full_name,
                    'tel' => $representative?->tel ?? null,
                    'address' => $address
                        ? ($address->address1 ?? '') 
                        . ($address->address2 ? ' ' . $address->address2 : '') 
                        . ($address->address3 ? ' ' . $address->address3 : '')
                        : null,
                    // 履歴事項全部証明書（★ここが正）
                    'history_certificate' => $document ? [
                        'path' => $document->path
                            ? Storage::url($document->path)
                            : null,
                        'thumbnail_path' => $document->thumbnail_path
                            ? Storage::url($document->thumbnail_path)
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

    // 編集画面
    public function edit(Member $member)
    {
        return Inertia::render('Admin/Members/Edit', [
            'member' => $member
        ]);
    }

    // 更新
    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
        ]);

        $member->update($validated);

        return redirect()->route('admin.members.index')
            ->with('success', __('profile.member_updated'));
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
