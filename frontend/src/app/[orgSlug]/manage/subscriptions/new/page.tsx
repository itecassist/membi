'use client';

import { useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { subscriptionsApi } from '@/lib/api/subscriptions';
import PageHeader from '@/components/ui/PageHeader';
import FormField from '@/components/ui/FormField';

const schema = z.object({
  name: z.string().min(1, 'Name is required'),
  description: z.string().optional(),
  membership_type: z.enum(['individual', 'group']),
  period: z.enum(['day', 'week', 'month', 'year', 'lifetime', 'none', 'instalments']),
  renewal_type: z.enum(['auto_renew', 'manual', 'not_renewable']),
  pricing_type: z.enum(['flat', 'family', 'corporate', 'tiered', 'custom_variable']),
  published: z.enum(['published', 'renewal_only', 'unpublished']),
  is_joining_fee: z.boolean(),
});

type FormData = z.infer<typeof schema>;

export default function ManageNewSubscriptionPage() {
  const { orgSlug } = useParams<{ orgSlug: string }>();
  const router = useRouter();
  const [apiError, setApiError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: {
      membership_type: 'individual',
      period: 'year',
      renewal_type: 'manual',
      pricing_type: 'flat',
      published: 'published',
      is_joining_fee: false,
    },
  });

  const onSubmit = async (data: FormData) => {
    setApiError(null);
    try {
      const res = await subscriptionsApi.create(orgSlug, {
        ...data,
        description: data.description || undefined,
      });
      router.push(`/${orgSlug}/manage/subscriptions/${res.data.data.id}`);
    } catch (err: unknown) {
      const msg =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ||
        'Failed to create subscription.';
      setApiError(msg);
    }
  };

  return (
    <>
      <PageHeader
        title="New subscription"
        description="Create a new subscription type for your organisation."
        backHref={`/${orgSlug}/manage/subscriptions`}
        backLabel="Subscriptions"
      />

      <div className="max-w-2xl">
        {apiError && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {apiError}
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
          <FormField
            as="input"
            label="Name"
            required
            id="name"
            placeholder="Annual Membership"
            error={errors.name?.message}
            {...register('name')}
          />

          <FormField
            as="textarea"
            label="Description"
            id="description"
            placeholder="A short description of this subscription."
            error={errors.description?.message}
            {...register('description')}
          />

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="select"
              label="Membership type"
              id="membership_type"
              error={errors.membership_type?.message}
              {...register('membership_type')}
            >
              <option value="individual">Individual</option>
              <option value="group">Group</option>
            </FormField>

            <FormField
              as="select"
              label="Period"
              id="period"
              error={errors.period?.message}
              {...register('period')}
            >
              <option value="day">Daily</option>
              <option value="week">Weekly</option>
              <option value="month">Monthly</option>
              <option value="year">Annual</option>
              <option value="lifetime">Lifetime</option>
              <option value="none">One-time</option>
              <option value="instalments">Instalments</option>
            </FormField>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="select"
              label="Renewal type"
              id="renewal_type"
              error={errors.renewal_type?.message}
              {...register('renewal_type')}
            >
              <option value="manual">Manual</option>
              <option value="auto_renew">Auto-renew</option>
              <option value="not_renewable">Not renewable</option>
            </FormField>

            <FormField
              as="select"
              label="Pricing type"
              id="pricing_type"
              error={errors.pricing_type?.message}
              {...register('pricing_type')}
            >
              <option value="flat">Flat fee</option>
              <option value="family">Family</option>
              <option value="corporate">Corporate</option>
              <option value="tiered">Tiered</option>
              <option value="custom_variable">Variable</option>
            </FormField>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="select"
              label="Published status"
              id="published"
              error={errors.published?.message}
              {...register('published')}
            >
              <option value="published">Published</option>
              <option value="renewal_only">Renewal only</option>
              <option value="unpublished">Unpublished</option>
            </FormField>
          </div>

          <div className="flex items-center gap-3">
            <input
              type="checkbox"
              id="is_joining_fee"
              className="w-4 h-4 rounded border-gray-300 text-emerald-600"
              {...register('is_joining_fee')}
            />
            <label htmlFor="is_joining_fee" className="text-sm text-gray-700 font-medium">
              This is a joining fee
            </label>
          </div>

          <div className="flex items-center gap-3 pt-2">
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {isSubmitting ? 'Creating…' : 'Create subscription'}
            </button>
            <button
              type="button"
              onClick={() => router.push(`/${orgSlug}/manage/subscriptions`)}
              className="px-5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </>
  );
}
