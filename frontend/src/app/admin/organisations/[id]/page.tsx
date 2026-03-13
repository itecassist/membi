'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { organisationsApi } from '@/lib/api/organisations';
import { membersApi } from '@/lib/api/members';
import type { Organisation, Member } from '@/types/api';
import PageHeader from '@/components/ui/PageHeader';
import Badge from '@/components/ui/Badge';

function Stat({ label, value }: { label: string; value: string | number }) {
  return (
    <div className="bg-white rounded-xl border border-gray-200 px-5 py-4">
      <p className="text-xs text-gray-500 mb-1">{label}</p>
      <p className="text-2xl font-semibold text-gray-900">{value}</p>
    </div>
  );
}

export default function OrgDetailPage() {
  const { id } = useParams<{ id: string }>();
  const router = useRouter();
  const [org, setOrg] = useState<Organisation | null>(null);
  const [recentMembers, setRecentMembers] = useState<Member[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!id) return;
    Promise.all([
      organisationsApi.get(id),
      membersApi.list(id, 1),
    ]).then(([orgRes, membersRes]) => {
      setOrg(orgRes.data.data);
      setRecentMembers(membersRes.data.data.slice(0, 5));
    }).catch(() => {}).finally(() => setLoading(false));
  }, [id]);

  if (loading) {
    return <div className="py-16 text-center text-sm text-gray-400">Loading…</div>;
  }

  if (!org) {
    return (
      <div className="py-16 text-center text-sm text-gray-500">
        Organisation not found.{' '}
        <Link href="/admin/organisations" className="text-primary-600 hover:underline">Back to list</Link>
      </div>
    );
  }

  return (
    <>
      <PageHeader
        title={org.name}
        description={org.email}
        backHref="/admin/organisations"
        backLabel="Organisations"
        action={{ label: 'Add member', href: `/admin/organisations/${id}/members/new` }}
      />

      {/* Status + quick info */}
      <div className="flex flex-wrap items-center gap-3 mb-8">
        <Badge active={org.is_active} />
        {org.website && (
          <a href={org.website} target="_blank" rel="noopener noreferrer"
            className="text-xs text-primary-600 hover:underline">
            {org.website}
          </a>
        )}
        {org.timezone && (
          <span className="text-xs text-gray-400">{org.timezone}</span>
        )}
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <Stat label="Slug" value={org.seo_name} />
        <Stat label="Phone" value={org.phone ?? '—'} />
        <Stat label="Free trial" value={org.free_trail ? 'Yes' : 'No'} />
        <Stat label="Created" value={new Date(org.created_at).toLocaleDateString()} />
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
          <Link href={`/admin/organisations/${id}/members`} className="text-xs text-primary-600 hover:underline">
            View all →
          </Link>
        </div>
        {recentMembers.length === 0 ? (
          <div className="py-12 text-center">
            <p className="text-sm text-gray-400 mb-3">No members yet.</p>
            <Link
              href={`/admin/organisations/${id}/members/new`}
              className="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:underline font-medium"
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
                  <td className="px-6 py-3 font-medium text-gray-900">{m.full_name}</td>
                  <td className="px-6 py-3 text-gray-500">{m.email}</td>
                  <td className="px-6 py-3"><Badge active={m.is_active} /></td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </>
  );
}
