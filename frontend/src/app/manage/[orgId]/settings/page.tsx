'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { organisationsApi } from '@/lib/api/organisations';
import type { Organisation } from '@/types/api';
import PageHeader from '@/components/ui/PageHeader';
import FormField from '@/components/ui/FormField';

const schema = z.object({
  name: z.string().min(1, 'Name is required'),
  email: z.string().email('Invalid email'),
  phone: z.string().optional(),
  website: z.string().url('Must be a valid URL').or(z.literal('')).optional(),
  description: z.string().optional(),
  timezone: z.string().optional(),
});

type FormData = z.infer<typeof schema>;

export default function ManageSettingsPage() {
  const { orgId } = useParams<{ orgId: string }>();
  const [org, setOrg] = useState<Organisation | null>(null);
  const [loading, setLoading] = useState(true);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);
  const [apiError, setApiError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting, isDirty },
  } = useForm<FormData>({ resolver: zodResolver(schema) });

  useEffect(() => {
    if (!orgId) return;
    organisationsApi.get(orgId)
      .then(({ data }) => {
        setOrg(data.data);
        reset({
          name: data.data.name,
          email: data.data.email,
          phone: data.data.phone ?? '',
          website: data.data.website ?? '',
          description: data.data.description ?? '',
          timezone: data.data.timezone ?? '',
        });
      })
      .catch(() => setApiError('Failed to load organisation.'))
      .finally(() => setLoading(false));
  }, [orgId, reset]);

  const onSubmit = async (data: FormData) => {
    setApiError(null);
    setSuccessMsg(null);
    try {
      await organisationsApi.update(orgId, {
        name: data.name,
        email: data.email,
        phone: data.phone || undefined,
        website: data.website || undefined,
        description: data.description || undefined,
        timezone: data.timezone || undefined,
      });
      setSuccessMsg('Settings saved.');
    } catch (err: unknown) {
      const msg =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ||
        'Failed to save settings.';
      setApiError(msg);
    }
  };

  if (loading) {
    return <div className="py-16 text-center text-sm text-gray-400">Loading…</div>;
  }

  return (
    <>
      <PageHeader
        title="Settings"
        description="Update your organisation details."
        backHref={`/manage/${orgId}`}
        backLabel="Dashboard"
      />

      <div className="max-w-2xl">
        {apiError && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{apiError}</div>
        )}
        {successMsg && (
          <div className="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">{successMsg}</div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="input"
              label="Organisation name"
              required
              id="name"
              error={errors.name?.message}
              {...register('name')}
            />
            <FormField
              as="input"
              type="email"
              label="Email"
              required
              id="email"
              error={errors.email?.message}
              {...register('email')}
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="input"
              label="Phone"
              id="phone"
              error={errors.phone?.message}
              {...register('phone')}
            />
            <FormField
              as="input"
              type="url"
              label="Website"
              id="website"
              placeholder="https://example.com"
              error={errors.website?.message}
              {...register('website')}
            />
          </div>

          <FormField
            as="input"
            label="Timezone"
            id="timezone"
            placeholder="Europe/London"
            error={errors.timezone?.message}
            {...register('timezone')}
          />

          <FormField
            as="textarea"
            label="Description"
            id="description"
            placeholder="A short description of your organisation."
            error={errors.description?.message}
            {...register('description')}
          />

          <div className="pt-2">
            <button
              type="submit"
              disabled={isSubmitting || !isDirty}
              className="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {isSubmitting ? 'Saving…' : 'Save settings'}
            </button>
          </div>
        </form>

        {org && (
          <div className="mt-6 bg-white rounded-xl border border-gray-200 p-6">
            <h3 className="text-sm font-semibold text-gray-900 mb-1">Slug</h3>
            <p className="text-xs text-gray-500 mb-2">The organisation slug cannot be changed after creation.</p>
            <code className="text-sm font-mono bg-gray-100 px-3 py-1.5 rounded-md text-gray-700">{org.seo_name}</code>
          </div>
        )}
      </div>
    </>
  );
}
