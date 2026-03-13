'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { subscriptionsApi } from '@/lib/api/subscriptions';
import type { Subscription } from '@/types/api';
import PageHeader from '@/components/ui/PageHeader';

const PERIOD_LABELS: Record<string, string> = {
  day: 'Daily', week: 'Weekly', month: 'Monthly', year: 'Annual',
  lifetime: 'Lifetime', none: 'One-time', instalments: 'Instalments',
};

const PUBLISHED_STYLES: Record<string, string> = {
  published: 'bg-emerald-50 text-emerald-700',
  renewal_only: 'bg-amber-50 text-amber-700',
  unpublished: 'bg-gray-100 text-gray-500',
};

const PUBLISHED_LABELS: Record<string, string> = {
  published: 'Published', renewal_only: 'Renewal only', unpublished: 'Unpublished',
};

export default function AdminSubscriptionsPage() {
  const { id: orgId } = useParams<{ id: string }>();
  const [subs, setSubs] = useState<Subscription[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!orgId) return;
    subscriptionsApi.list(orgId)
      .then(({ data }) => setSubs(data.data))
      .catch(() => setError('Failed to load subscriptions.'))
      .finally(() => setLoading(false));
  }, [orgId]);

  return (
    <>
      <PageHeader
        title="Subscriptions"
        description="Subscription types configured for this organisation."
        backHref={`/admin/organisations/${orgId}`}
        backLabel="Overview"
        action={{ label: 'New subscription', href: `/admin/organisations/${orgId}/subscriptions/new` }}
      />

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{error}</div>
      )}

      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="py-16 text-center text-sm text-gray-400">Loading…</div>
        ) : subs.length === 0 ? (
          <div className="py-16 text-center">
            <p className="text-sm text-gray-400 mb-4">No subscription types yet.</p>
            <Link
              href={`/admin/organisations/${orgId}/subscriptions/new`}
              className="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors"
            >
              Create first subscription
            </Link>
          </div>
        ) : (
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 bg-gray-50">
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pricing</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th className="px-6 py-3" />
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {subs.map((sub) => (
                <tr key={sub.id} className="hover:bg-gray-50 transition-colors">
                  <td className="px-6 py-4">
                    <div className="font-medium text-gray-900">{sub.name}</div>
                    {sub.description && (
                      <div className="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{sub.description}</div>
                    )}
                  </td>
                  <td className="px-6 py-4 text-gray-600">{PERIOD_LABELS[sub.period] ?? sub.period}</td>
                  <td className="px-6 py-4 text-gray-600 capitalize">{sub.pricing_type.replace('_', ' ')}</td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${PUBLISHED_STYLES[sub.published] ?? 'bg-gray-100 text-gray-500'}`}>
                      {PUBLISHED_LABELS[sub.published] ?? sub.published}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-right">
                    <Link
                      href={`/admin/organisations/${orgId}/subscriptions/${sub.id}`}
                      className="text-primary-600 hover:text-primary-800 font-medium text-xs"
                    >
                      Manage →
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </>
  );
}
