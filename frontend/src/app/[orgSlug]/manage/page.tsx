'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { membersApi } from '@/lib/api/members';
import { useAuthStore } from '@/store/authStore';
import type { Member } from '@/types/api';
import Badge from '@/components/ui/Badge';

function StatCard({ label, value, href }: { label: string; value: string | number; href?: string }) {
  const content = (
    <div className="bg-white rounded-xl border border-gray-200 px-5 py-4 hover:shadow-sm transition-shadow">
      <p className="text-xs text-gray-500 mb-1">{label}</p>
      <p className="text-2xl font-semibold text-gray-900">{value}</p>
    </div>
  );
  return href ? <Link href={href}>{content}</Link> : content;
}

export default function ManageDashboardPage() {
  const { orgSlug } = useParams<{ orgSlug: string }>();
  const { organisations } = useAuthStore();
  const org = organisations.find((o) => o.seo_name === orgSlug) ?? null;
  const [recentMembers, setRecentMembers] = useState<Member[]>([]);
  const [memberTotal, setMemberTotal] = useState<number>(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!orgSlug) return;
    membersApi.list(orgSlug, 1)
      .then((membersRes) => {
        setRecentMembers(membersRes.data.data.slice(0, 5));
        setMemberTotal(membersRes.data.meta?.total ?? membersRes.data.data.length);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [orgSlug]);

  if (loading) {
    return <div className="py-16 text-center text-sm text-gray-400">Loading…</div>;
  }

  if (!org) {
    return <div className="py-16 text-center text-sm text-gray-500">Organisation not found.</div>;
  }

  return (
    <>
      {/* Header */}
      <div className="mb-8">
        <div className="flex items-center gap-3 mb-1">
          <h1 className="text-2xl font-semibold text-gray-900">{org.name}</h1>
          <Badge active={org.is_active} />
        </div>
        <p className="text-sm text-gray-500">{org.email}</p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <StatCard label="Total members" value={memberTotal} href={`/${orgSlug}/manage/members`} />
        <StatCard label="Slug" value={org.seo_name} />
        <StatCard label="Timezone" value={org.timezone ?? '—'} />
        <StatCard label="Member portal" value="Active" />
      </div>

      {/* Description */}
      {org.description && (
        <div className="bg-white rounded-xl border border-gray-200 px-5 py-4 mb-8">
          <p className="text-xs text-gray-400 mb-1 uppercase tracking-wide font-medium">About</p>
          <p className="text-sm text-gray-700">{org.description}</p>
        </div>
      )}

      {/* Recent members */}
      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 className="text-sm font-semibold text-gray-900">Recent members</h2>
          <Link href={`/${orgSlug}/manage/members`} className="text-xs text-emerald-600 hover:underline">
            View all →
          </Link>
        </div>

        {recentMembers.length === 0 ? (
          <div className="py-12 text-center">
            <p className="text-sm text-gray-400 mb-3">No members yet.</p>
            <Link
              href={`/${orgSlug}/manage/members/new`}
              className="inline-flex items-center gap-1.5 text-sm text-emerald-600 hover:underline font-medium"
            >
              Add first member →
            </Link>
          </div>
        ) : (
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-gray-50 border-b border-gray-100">
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {recentMembers.map((m) => (
                <tr key={m.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4 font-medium text-gray-900">{m.full_name}</td>
                  <td className="px-6 py-4 text-gray-600">{m.email}</td>
                  <td className="px-6 py-4"><Badge active={m.is_active} /></td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </>
  );
}
