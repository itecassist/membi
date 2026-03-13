'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { ordersApi } from '@/lib/api/orders';
import type { Order, PaginatedResponse } from '@/types/api';
import PageHeader from '@/components/ui/PageHeader';

const STATUS_STYLES: Record<string, { bg: string; text: string; label: string }> = {
  pending:              { bg: 'bg-amber-50',   text: 'text-amber-700',   label: 'Pending' },
  payment_received:     { bg: 'bg-blue-50',    text: 'text-blue-700',    label: 'Payment received' },
  completed:            { bg: 'bg-emerald-50', text: 'text-emerald-700', label: 'Completed' },
  cancelled:            { bg: 'bg-red-50',     text: 'text-red-600',     label: 'Cancelled' },
  payment_problem:      { bg: 'bg-red-50',     text: 'text-red-700',     label: 'Payment problem' },
  no_payment_required:  { bg: 'bg-emerald-50', text: 'text-emerald-600', label: 'No payment req.' },
  partial_payment:      { bg: 'bg-orange-50',  text: 'text-orange-700',  label: 'Partial payment' },
  refunded:             { bg: 'bg-gray-100',   text: 'text-gray-500',    label: 'Refunded' },
};

function fmtDate(iso: string) {
  return new Date(iso).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function fmtCurrency(amount: string, currency: string) {
  return new Intl.NumberFormat('en-GB', { style: 'currency', currency }).format(Number(amount));
}

export default function AdminOrgOrdersPage() {
  const { id: orgId } = useParams<{ id: string }>();
  const [orders, setOrders] = useState<Order[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Order>['meta'] | null>(null);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!orgId) return;
    setLoading(true);
    ordersApi.list(orgId, page)
      .then(({ data }) => {
        setOrders(data.data);
        setMeta(data.meta);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [orgId, page]);

  return (
    <>
      <PageHeader
        title="Orders"
        description="Orders placed by members of this organisation."
        backHref={`/admin/organisations/${orgId}`}
        backLabel="Organisation"
      />

      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="py-16 text-center text-sm text-gray-400">Loading orders…</div>
        ) : orders.length === 0 ? (
          <div className="py-16 text-center text-sm text-gray-400">No orders found.</div>
        ) : (
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-gray-50 border-b border-gray-100">
                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Member</th>
                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Email</th>
                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th className="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {orders.map((order) => {
                const style = STATUS_STYLES[order.status] ?? { bg: 'bg-gray-100', text: 'text-gray-500', label: order.status };
                return (
                  <tr key={order.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-5 py-3 text-gray-600 whitespace-nowrap">{fmtDate(order.date_placed)}</td>
                    <td className="px-5 py-3 font-medium text-gray-900">{order.name}</td>
                    <td className="px-5 py-3 text-gray-500 hidden md:table-cell">{order.email}</td>
                    <td className="px-5 py-3">
                      <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${style.bg} ${style.text}`}>
                        {style.label}
                      </span>
                    </td>
                    <td className="px-5 py-3 text-right font-semibold text-gray-900 tabular-nums">
                      {fmtCurrency(order.total, order.currency_code)}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        )}

        {meta && meta.last_page > 1 && (
          <div className="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-600">
            <span>Page {meta.current_page} of {meta.last_page} &middot; {meta.total} orders</span>
            <div className="flex gap-2">
              <button
                disabled={page === 1}
                onClick={() => setPage((p) => p - 1)}
                className="px-3 py-1 rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50 transition-colors text-xs"
              >
                Previous
              </button>
              <button
                disabled={page === meta.last_page}
                onClick={() => setPage((p) => p + 1)}
                className="px-3 py-1 rounded-lg border border-gray-200 disabled:opacity-40 hover:bg-gray-50 transition-colors text-xs"
              >
                Next
              </button>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
