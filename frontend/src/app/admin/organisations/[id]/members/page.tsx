'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { membersApi } from '@/lib/api/members';
import type { Member, PaginatedResponse } from '@/types/api';
import { useAuthStore } from '@/store/authStore';
import PageHeader from '@/components/ui/PageHeader';
import Badge from '@/components/ui/Badge';

export default function MembersPage() {
  const { id: orgId } = useParams<{ id: string }>();
  const currentOrg = useAuthStore((s) => s.currentOrganisation);
  const [members, setMembers] = useState<Member[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Member>['meta'] | null>(null);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!orgId) return;
    setLoading(true);
    membersApi.list(orgId, page)
      .then(({ data }) => {
        setMembers(data.data);
        setMeta(data.meta);
      })
      .catch(() => setError('Failed to load members.'))
      .finally(() => setLoading(false));
  }, [orgId, page]);

  const orgName = currentOrg?.name ?? 'Organisation';

  return (
    <>
      <PageHeader
        title="Members"
        description={`All members of ${orgName}.`}
        backHref={`/admin/organisations/${orgId}`}
        backLabel={orgName}
        action={{ label: 'Add member', href: `/admin/organisations/${orgId}/members/new` }}
      />

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{error}</div>
      )}

      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="py-16 text-center text-sm text-gray-400">Loading…</div>
        ) : members.length === 0 ? (
          <div className="py-16 text-center">
            <p className="text-sm text-gray-400 mb-4">No members yet.</p>
            <Link
              href={`/admin/organisations/${orgId}/members/new`}
              className="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors"
            >
              Add first member
            </Link>
          </div>
        ) : (
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 bg-gray-50">
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Member #</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Joined</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {members.map((m) => (
                <tr key={m.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4">
                    <div className="font-medium text-gray-900">{m.full_name}</div>
                    {m.classification && (
                      <div className="text-xs text-gray-400">{m.classification}</div>
                    )}
                  </td>
                  <td className="px-6 py-4 text-gray-600">{m.email}</td>
                  <td className="px-6 py-4 text-gray-500 font-mono text-xs">
                    {m.member_number ?? '—'}
                  </td>
                  <td className="px-6 py-4 text-gray-500">
                    {m.joined_at ? new Date(m.joined_at).toLocaleDateString() : '—'}
                  </td>
                  <td className="px-6 py-4"><Badge active={m.is_active} /></td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {meta && meta.last_page > 1 && (
        <div className="mt-6 flex items-center justify-between text-sm text-gray-500">
          <p>Showing {meta.from}–{meta.to} of {meta.total}</p>
          <div className="flex gap-2">
            <button
              disabled={page === 1}
              onClick={() => setPage((p) => p - 1)}
              className="px-3 py-1.5 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <button
              disabled={page === meta.last_page}
              onClick={() => setPage((p) => p + 1)}
              className="px-3 py-1.5 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      )}
    </>
  );
}
