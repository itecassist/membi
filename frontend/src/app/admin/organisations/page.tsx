'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { organisationsApi } from '@/lib/api/organisations';
import type { Organisation, PaginatedResponse } from '@/types/api';
import PageHeader from '@/components/ui/PageHeader';
import Badge from '@/components/ui/Badge';

export default function OrganisationsPage() {
  const [orgs, setOrgs] = useState<Organisation[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Organisation>['meta'] | null>(null);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    organisationsApi.list(page)
      .then(({ data }) => {
        setOrgs(data.data);
        setMeta(data.meta);
      })
      .catch(() => setError('Failed to load organisations.'))
      .finally(() => setLoading(false));
  }, [page]);

  return (
    <>
      <PageHeader
        title="Organisations"
        description="All organisations on the Membix platform."
        action={{ label: 'New organisation', href: '/admin/organisations/new' }}
      />

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
          {error}
        </div>
      )}

      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="py-16 text-center text-sm text-gray-400">Loading…</div>
        ) : orgs.length === 0 ? (
          <div className="py-16 text-center">
            <p className="text-gray-400 text-sm mb-4">No organisations yet.</p>
            <Link
              href="/admin/organisations/new"
              className="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors"
            >
              Create your first organisation
            </Link>
          </div>
        ) : (
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 bg-gray-50">
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Slug</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th className="px-6 py-3" />
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {orgs.map((org) => (
                <tr key={org.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4 font-medium text-gray-900">{org.name}</td>
                  <td className="px-6 py-4 text-gray-500 font-mono text-xs">{org.seo_name}</td>
                  <td className="px-6 py-4 text-gray-600">{org.email}</td>
                  <td className="px-6 py-4">
                    <Badge active={org.is_active} />
                  </td>
                  <td className="px-6 py-4 text-right">
                    <Link
                      href={`/admin/organisations/${org.id}`}
                      className="text-primary-600 hover:text-primary-800 font-medium text-xs"
                    >
                      View →
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div className="mt-6 flex items-center justify-between text-sm text-gray-500">
          <p>
            Showing {meta.from}–{meta.to} of {meta.total}
          </p>
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
