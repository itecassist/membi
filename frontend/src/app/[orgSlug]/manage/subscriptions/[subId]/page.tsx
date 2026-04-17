'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { subscriptionsApi } from '@/lib/api/subscriptions';
import type { Subscription, SubscriptionPriceOption } from '@/types/api';
import PageHeader from '@/components/ui/PageHeader';
import FormField from '@/components/ui/FormField';

const PERIOD_LABELS: Record<string, string> = {
  day: 'Daily', week: 'Weekly', month: 'Monthly', year: 'Annual',
  lifetime: 'Lifetime', none: 'One-time', instalments: 'Instalments',
};

const RENEWAL_LABELS: Record<string, string> = {
  auto_renew: 'Auto-renew', manual: 'Manual', not_renewable: 'Not renewable',
};

const PRICING_LABELS: Record<string, string> = {
  flat: 'Flat fee', family: 'Family', corporate: 'Corporate', tiered: 'Tiered', custom_variable: 'Variable',
};

const priceOptionSchema = z.object({
  name: z.string().min(1, 'Name is required'),
  eligibility: z.enum(['individual', 'adult', 'junior', 'family', 'corporate']),
  pricing_type: z.enum(['flat', 'tiered', 'custom_variable']),
  price: z.number().min(0, 'Price must be ≥ 0'),
  published: z.enum(['published', 'renewals_only', 'unpublished']),
});
type PriceOptionForm = z.infer<typeof priceOptionSchema>;

function PriceOptionRow({
  option,
  onDelete,
}: {
  option: SubscriptionPriceOption;
  onDelete: (id: string) => void;
}) {
  return (
    <tr className="hover:bg-gray-50 transition-colors">
      <td className="px-5 py-3 font-medium text-gray-900">{option.name}</td>
      <td className="px-5 py-3 text-gray-600 capitalize">{option.eligibility}</td>
      <td className="px-5 py-3 text-gray-600">
        {option.pricing_type === 'flat'
          ? `£${Number(option.price).toFixed(2)}`
          : option.pricing_type === 'tiered'
          ? `£${Number(option.price_min ?? 0).toFixed(2)} – £${Number(option.price_max ?? 0).toFixed(2)}`
          : 'Variable'}
      </td>
      <td className="px-5 py-3">
        <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${
          option.published === 'published'
            ? 'bg-emerald-50 text-emerald-700'
            : option.published === 'renewals_only'
            ? 'bg-amber-50 text-amber-700'
            : 'bg-gray-100 text-gray-500'
        }`}>
          {option.published === 'published' ? 'Published' : option.published === 'renewals_only' ? 'Renewals only' : 'Unpublished'}
        </span>
      </td>
      <td className="px-5 py-3 text-right">
        <button
          onClick={() => onDelete(option.id)}
          className="text-red-500 hover:text-red-700 text-xs font-medium"
        >
          Remove
        </button>
      </td>
    </tr>
  );
}

export default function ManageSubscriptionDetailPage() {
  const { orgSlug, subId } = useParams<{ orgSlug: string; subId: string }>();
  const router = useRouter();
  const [sub, setSub] = useState<Subscription | null>(null);
  const [priceOptions, setPriceOptions] = useState<SubscriptionPriceOption[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddOption, setShowAddOption] = useState(false);
  const [addingOption, setAddingOption] = useState(false);
  const [optionError, setOptionError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<PriceOptionForm>({
    resolver: zodResolver(priceOptionSchema),
    defaultValues: { eligibility: 'individual', pricing_type: 'flat', price: 0, published: 'published' },
  });

  useEffect(() => {
    if (!orgSlug || !subId) return;
    subscriptionsApi.get(orgSlug, subId)
      .then(({ data }) => {
        setSub(data.data);
        setPriceOptions(data.data.price_options ?? []);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [orgSlug, subId]);

  const handleDeleteOption = async (optionId: string) => {
    if (!confirm('Remove this price option?')) return;
    await subscriptionsApi.deletePriceOption(orgSlug, subId, optionId).catch(() => {});
    setPriceOptions((prev) => prev.filter((o) => o.id !== optionId));
  };

  const onAddOption = async (data: PriceOptionForm) => {
    setOptionError(null);
    setAddingOption(true);
    try {
      const res = await subscriptionsApi.addPriceOption(orgSlug, subId, data);
      setPriceOptions((prev) => [...prev, res.data.data]);
      reset();
      setShowAddOption(false);
    } catch (err: unknown) {
      const msg =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ||
        'Failed to add price option.';
      setOptionError(msg);
    } finally {
      setAddingOption(false);
    }
  };

  if (loading) return <div className="py-16 text-center text-sm text-gray-400">Loading…</div>;
  if (!sub) return <div className="py-16 text-center text-sm text-gray-500">Subscription not found.</div>;

  return (
    <>
      <PageHeader
        title={sub.name}
        description={sub.description ?? undefined}
        backHref={`/${orgSlug}/manage/subscriptions`}
        backLabel="Subscriptions"
      />

      {/* Details card */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        {[
          { label: 'Period', value: PERIOD_LABELS[sub.period] ?? sub.period },
          { label: 'Renewal', value: RENEWAL_LABELS[sub.renewal_type] ?? sub.renewal_type },
          { label: 'Pricing', value: PRICING_LABELS[sub.pricing_type] ?? sub.pricing_type },
          {
            label: 'Status',
            value: sub.published === 'published' ? 'Published' : sub.published === 'renewal_only' ? 'Renewal only' : 'Unpublished',
          },
        ].map(({ label, value }) => (
          <div key={label} className="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <p className="text-xs text-gray-500 mb-1">{label}</p>
            <p className="text-sm font-semibold text-gray-900">{value}</p>
          </div>
        ))}
      </div>

      {/* Price options */}
      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 className="text-sm font-semibold text-gray-900">Price options</h2>
          <button
            onClick={() => setShowAddOption((v) => !v)}
            className="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 hover:text-emerald-800 transition-colors"
          >
            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add option
          </button>
        </div>

        {priceOptions.length === 0 ? (
          <div className="py-10 text-center text-sm text-gray-400">No price options yet. Add one above.</div>
        ) : (
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-gray-50 border-b border-gray-100">
                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Eligibility</th>
                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Price</th>
                <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th className="px-5 py-3" />
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {priceOptions.map((opt) => (
                <PriceOptionRow key={opt.id} option={opt} onDelete={handleDeleteOption} />
              ))}
            </tbody>
          </table>
        )}

        {/* Inline add form */}
        {showAddOption && (
          <div className="border-t border-gray-200 bg-gray-50 px-6 py-5">
            <p className="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-4">New price option</p>
            {optionError && (
              <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{optionError}</div>
            )}
            <form onSubmit={handleSubmit(onAddOption)} className="space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <FormField
                  as="input"
                  label="Name"
                  required
                  id="option_name"
                  placeholder="Adult"
                  error={errors.name?.message}
                  {...register('name')}
                />
                <FormField
                  as="select"
                  label="Eligibility"
                  id="eligibility"
                  error={errors.eligibility?.message}
                  {...register('eligibility')}
                >
                  <option value="individual">Individual</option>
                  <option value="adult">Adult</option>
                  <option value="junior">Junior</option>
                  <option value="family">Family</option>
                  <option value="corporate">Corporate</option>
                </FormField>
                <FormField
                  as="select"
                  label="Pricing type"
                  id="option_pricing_type"
                  error={errors.pricing_type?.message}
                  {...register('pricing_type')}
                >
                  <option value="flat">Flat fee</option>
                  <option value="tiered">Tiered</option>
                  <option value="custom_variable">Variable</option>
                </FormField>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <FormField
                  as="input"
                  type="number"
                  label="Price (£)"
                  required
                  id="price"
                  placeholder="0.00"
                  error={errors.price?.message}
                  {...register('price', { valueAsNumber: true })}
                />
                <FormField
                  as="select"
                  label="Published"
                  id="option_published"
                  error={errors.published?.message}
                  {...register('published')}
                >
                  <option value="published">Published</option>
                  <option value="renewals_only">Renewals only</option>
                  <option value="unpublished">Unpublished</option>
                </FormField>
              </div>
              <div className="flex gap-3">
                <button
                  type="submit"
                  disabled={addingOption}
                  className="px-4 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                >
                  {addingOption ? 'Adding…' : 'Add option'}
                </button>
                <button
                  type="button"
                  onClick={() => setShowAddOption(false)}
                  className="px-4 py-1.5 text-sm text-gray-600 hover:text-gray-900 transition-colors"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        )}
      </div>

      {/* Danger zone */}
      <div className="bg-white rounded-xl border border-red-200 p-6">
        <h3 className="text-sm font-semibold text-gray-900 mb-1">Danger zone</h3>
        <p className="text-xs text-gray-500 mb-3">Permanently delete this subscription type. This cannot be undone.</p>
        <button
          onClick={async () => {
            if (!confirm(`Delete "${sub.name}"? This cannot be undone.`)) return;
            await subscriptionsApi.delete(orgSlug, subId).catch(() => {});
            router.push(`/${orgSlug}/manage/subscriptions`);
          }}
          className="px-4 py-1.5 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition-colors"
        >
          Delete subscription
        </button>
      </div>
    </>
  );
}
