'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import { subscriptionsApi } from '@/lib/api/subscriptions';
import type { Subscription, SubscriptionStats, SubscriptionStatsRow } from '@/types/api';
import PageHeader from '@/components/ui/PageHeader';

// ── Constants ────────────────────────────────────────────────────────────────

type Tab = 'overview' | 'subscriptions' | 'renewals' | 'configuration';

const PERIOD_LABELS: Record<string, string> = {
  day: 'Daily', week: 'Weekly', month: 'Monthly', year: 'Annual',
  lifetime: 'Lifetime', none: 'One-time', instalments: 'Instalments',
};

const PUBLISHED_STYLES: Record<string, string> = {
  published:    'bg-emerald-50 text-emerald-700',
  renewal_only: 'bg-amber-50 text-amber-700',
  unpublished:  'bg-gray-100 text-gray-500',
};

const PUBLISHED_LABELS: Record<string, string> = {
  published: 'Published', renewal_only: 'Renewal only', unpublished: 'Unpublished',
};

// ── Small helpers ─────────────────────────────────────────────────────────────

function StatCard({ label, value, sub }: { label: string; value: number; sub?: string }) {
  return (
    <div className="flex flex-col">
      <span className="text-xs text-gray-500">{label}</span>
      <div className="flex items-baseline gap-1.5 mt-0.5">
        <span className="text-lg font-bold text-gray-900">{value}</span>
        {sub && <span className="text-xs text-gray-400">{sub}</span>}
      </div>
    </div>
  );
}

function Num({ n }: { n: number }) {
  return (
    <span className={n > 0 ? 'text-emerald-600 font-semibold' : 'text-gray-400'}>{n}</span>
  );
}

// ── Overview tab ──────────────────────────────────────────────────────────────

function OverviewTab({ stats, orgSlug }: { stats: SubscriptionStats; orgSlug: string }) {
  const [category, setCategory] = useState<'individual' | 'group'>('individual');
  const [expanded, setExpanded] = useState(false);

  const rows = stats.breakdown.filter((r) => r.membership_type === category);

  return (
    <div className="space-y-5">
      {/* Summary banners */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {/* Current */}
        <div className="bg-white rounded-xl border border-gray-200 p-5">
          <h3 className="text-sm font-semibold text-gray-700 mb-4">Current</h3>
          <div className="space-y-2.5">
            <div className="flex items-center justify-between text-sm">
              <span className="text-gray-500">Membership subscriptions</span>
              <span className="font-semibold text-gray-900">{stats.summary.membership_subscriptions}</span>
            </div>
            <div className="flex items-center justify-between text-sm">
              <span className="text-gray-500">Other subscriptions</span>
              <span className="font-semibold text-gray-900">{stats.summary.other_subscriptions}</span>
            </div>
            <div className="flex items-center justify-between text-sm border-t border-gray-100 pt-2.5">
              <span className="text-gray-500">Members</span>
              <span className="font-semibold text-gray-900">{stats.summary.members}</span>
            </div>
          </div>
        </div>

        {/* Recently expired */}
        <div className="bg-white rounded-xl border border-gray-200 p-5">
          <h3 className="text-sm font-semibold text-gray-700 mb-4">Recently expired</h3>
          <div className="space-y-2.5">
            <div className="flex items-center justify-between text-sm">
              <span className="text-gray-500">In last 30 days</span>
              <span className="font-semibold text-gray-900">{stats.summary.expired_last_30_days}</span>
            </div>
            <div className="flex items-center justify-between text-sm">
              <span className="text-gray-500">Pending payment</span>
              <span className="font-semibold text-gray-900">{stats.summary.pending_payment}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Breakdown table */}
      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {/* Table toolbar */}
        <div className="px-5 py-3 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
          <div className="flex items-center gap-1 bg-gray-100 rounded-lg p-0.5">
            {(['individual', 'group'] as const).map((cat) => (
              <button
                key={cat}
                onClick={() => setCategory(cat)}
                className={`px-4 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  category === cat ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                }`}
              >
                {cat === 'individual' ? 'Membership' : 'Other'}
              </button>
            ))}
          </div>
          <button
            onClick={() => setExpanded((v) => !v)}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
              {expanded
                ? <path strokeLinecap="round" strokeLinejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
                : <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />}
            </svg>
            {expanded ? 'Collapse options' : 'Expand type options'}
          </button>
        </div>

        {rows.length === 0 ? (
          <div className="py-10 text-center text-sm text-gray-400">
            No {category === 'individual' ? 'membership' : 'other'} subscription types.{' '}
            <Link href={`/${orgSlug}/manage/subscriptions/new`} className="text-emerald-600 hover:underline">
              Create one
            </Link>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm min-w-160">
              <thead>
                <tr className="bg-gray-50 border-b border-gray-100 text-xs">
                  <th className="text-left px-5 py-3 font-semibold text-gray-500 uppercase tracking-wide w-48">Sub type</th>
                  <th className="text-left px-3 py-3 font-semibold text-gray-500 uppercase tracking-wide" colSpan={expanded ? 5 : 5}>
                    <span className="text-gray-400 font-normal">Current</span>
                  </th>
                  <th className="text-left px-3 py-3 font-semibold text-gray-500 uppercase tracking-wide">
                    <span className="text-gray-400 font-normal">Recently expired</span>
                  </th>
                  <th className="px-3 py-3 w-10" />
                </tr>
                <tr className="border-b border-gray-100 bg-gray-50/50 text-xs">
                  <th className="px-5 py-2" />
                  <th className="text-left px-3 py-2 font-semibold text-gray-500 uppercase tracking-wide">Subscriptions</th>
                  <th className="text-left px-3 py-2 font-semibold text-gray-500 uppercase tracking-wide">Members</th>
                  <th className="text-left px-3 py-2 font-semibold text-gray-500 uppercase tracking-wide">Auto-renew</th>
                  <th className="text-left px-3 py-2 font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Renewable</th>
                  <th className="text-left px-3 py-2 font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Not renewing</th>
                  <th className="text-left px-3 py-2 font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                  <th className="px-3 py-2" />
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {rows.map((row) => (
                  <BreakdownRow key={row.id} row={row} orgSlug={orgSlug} expanded={expanded} />
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}

function BreakdownRow({ row, orgSlug, expanded }: { row: SubscriptionStatsRow; orgSlug: string; expanded: boolean }) {
  return (
    <>
      <tr className="hover:bg-gray-50 transition-colors">
        <td className="px-5 py-3 font-medium text-gray-900 leading-snug">
          <Link href={`/${orgSlug}/manage/subscriptions/${row.id}`} className="hover:text-emerald-700 transition-colors">
            {row.name}
          </Link>
          <div className="text-xs text-gray-400 mt-0.5">{PERIOD_LABELS[row.period] ?? row.period}</div>
        </td>
        <td className="px-3 py-3"><Num n={row.active_count} /></td>
        <td className="px-3 py-3"><Num n={row.members_count} /></td>
        <td className="px-3 py-3"><Num n={row.auto_renew} /></td>
        <td className="px-3 py-3 hidden lg:table-cell"><Num n={row.manual_renew} /></td>
        <td className="px-3 py-3 hidden lg:table-cell"><Num n={row.not_renewable} /></td>
        <td className="px-3 py-3 text-gray-600">{row.recently_expired} / 0</td>
        <td className="px-3 py-3">
          <Link href={`/${orgSlug}/manage/subscriptions/${row.id}`} className="text-xs text-emerald-600 hover:text-emerald-800 font-medium whitespace-nowrap">
            Manage →
          </Link>
        </td>
      </tr>
      {expanded && (
        <tr className="bg-emerald-50/30">
          <td colSpan={8} className="px-5 py-2 text-xs text-gray-500 italic">
            Price options for <span className="font-medium not-italic text-gray-700">{row.name}</span> — open subscription to manage.
          </td>
        </tr>
      )}
    </>
  );
}

// ── Subscriptions list tab ────────────────────────────────────────────────────

function SubscriptionsTab({ subs, orgSlug }: { subs: Subscription[]; orgSlug: string }) {
  return (
    <div>
      <div className="flex justify-end mb-4">
        <Link
          href={`/${orgSlug}/manage/subscriptions/new`}
          className="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          New subscription
        </Link>
      </div>

      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {subs.length === 0 ? (
          <div className="py-16 text-center">
            <p className="text-sm text-gray-400 mb-4">No subscription types yet.</p>
            <Link
              href={`/${orgSlug}/manage/subscriptions/new`}
              className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors"
            >
              Create first subscription
            </Link>
          </div>
        ) : (
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 bg-gray-50">
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Type</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                <th className="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Pricing</th>
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
                  <td className="px-6 py-4 text-gray-500 capitalize hidden md:table-cell">
                    {sub.membership_type === 'individual' ? 'Membership' : 'Group'}
                  </td>
                  <td className="px-6 py-4 text-gray-600">{PERIOD_LABELS[sub.period ?? ''] ?? sub.period}</td>
                  <td className="px-6 py-4 text-gray-600 capitalize hidden lg:table-cell">{sub.pricing_type?.replace('_', ' ')}</td>
                  <td className="px-6 py-4">
                    <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${PUBLISHED_STYLES[sub.published ?? ''] ?? 'bg-gray-100 text-gray-500'}`}>
                      {PUBLISHED_LABELS[sub.published ?? ''] ?? sub.published}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-right">
                    <Link href={`/${orgSlug}/manage/subscriptions/${sub.id}`} className="text-emerald-600 hover:text-emerald-800 font-medium text-xs">
                      Manage →
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}

// ── Renewals tab ──────────────────────────────────────────────────────────────

function RenewalsTab() {
  return (
    <div className="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <div className="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
        <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" strokeWidth={1.5} viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
      </div>
      <h3 className="text-sm font-semibold text-gray-900 mb-1">Renewals</h3>
      <p className="text-sm text-gray-500">Automated renewal management and upcoming renewals view coming soon.</p>
    </div>
  );
}

// ── Configuration tab ─────────────────────────────────────────────────────────

function ConfigurationTab({ orgSlug }: { orgSlug: string }) {
  return (
    <div className="bg-white rounded-xl border border-gray-200 p-6">
      <h3 className="text-sm font-semibold text-gray-900 mb-1">Subscription configuration</h3>
      <p className="text-sm text-gray-500 mb-4">Manage default settings, joining fees, and renewal policies for your organisation.</p>
      <Link
        href={`/${orgSlug}/manage/settings`}
        className="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
      >
        <svg className="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
          <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        Organisation settings
      </Link>
    </div>
  );
}

// ── Page ──────────────────────────────────────────────────────────────────────

const TABS: { id: Tab; label: string }[] = [
  { id: 'overview',       label: 'Overview' },
  { id: 'subscriptions',  label: 'View subscriptions' },
  { id: 'renewals',       label: 'Renewals' },
  { id: 'configuration',  label: 'Configuration' },
];

export default function ManageSubscriptionsPage() {
  const { orgSlug } = useParams<{ orgSlug: string }>();
  const [tab, setTab] = useState<Tab>('overview');
  const [subs, setSubs] = useState<Subscription[]>([]);
  const [stats, setStats] = useState<SubscriptionStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!orgSlug) return;
    Promise.all([
      subscriptionsApi.list(orgSlug),
      subscriptionsApi.stats(orgSlug),
    ])
      .then(([subsRes, statsRes]) => {
        setSubs(subsRes.data.data);
        setStats(statsRes.data.data);
      })
      .catch(() => setError('Failed to load subscriptions.'))
      .finally(() => setLoading(false));
  }, [orgSlug]);

  return (
    <>
      <PageHeader
        title="Subscriptions"
        description="Manage your organisation's subscriptions here."
        backHref={`/${orgSlug}/manage`}
        backLabel="Dashboard"
      />

      {error && (
        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{error}</div>
      )}

      {loading ? (
        <div className="py-20 text-center text-sm text-gray-400">Loading…</div>
      ) : (
        <>
          {/* Tab bar */}
          <div className="flex items-center gap-1 border-b border-gray-200 mb-6 -mx-1 px-1">
            {TABS.map((t) => (
              <button
                key={t.id}
                onClick={() => setTab(t.id)}
                className={`px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap ${
                  tab === t.id
                    ? 'border-emerald-600 text-emerald-700'
                    : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'
                }`}
              >
                {t.label}
              </button>
            ))}
          </div>

          {/* Tab content */}
          {tab === 'overview' && stats && <OverviewTab stats={stats} orgSlug={orgSlug} />}
          {tab === 'subscriptions' && <SubscriptionsTab subs={subs} orgSlug={orgSlug} />}
          {tab === 'renewals' && <RenewalsTab />}
          {tab === 'configuration' && <ConfigurationTab orgSlug={orgSlug} />}
        </>
      )}
    </>
  );
}
