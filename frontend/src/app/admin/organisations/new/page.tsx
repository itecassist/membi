'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { organisationsApi } from '@/lib/api/organisations';
import PageHeader from '@/components/ui/PageHeader';
import FormField from '@/components/ui/FormField';

const schema = z.object({
  name: z.string().min(1, 'Name is required'),
  seo_name: z
    .string()
    .min(1, 'Slug is required')
    .max(64, 'Max 64 characters')
    .regex(/^[a-z0-9-]+$/, 'Lowercase letters, numbers and hyphens only'),
  email: z.string().email('Invalid email'),
  phone: z.string().optional(),
  website: z.string().url('Must be a valid URL').or(z.literal('')).optional(),
  description: z.string().optional(),
  timezone: z.string().optional(),
  is_active: z.boolean(),
});

type FormData = z.infer<typeof schema>;

export default function NewOrganisationPage() {
  const router = useRouter();
  const [apiError, setApiError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    setValue,
    watch,
    formState: { errors, isSubmitting },
  } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { is_active: true },
  });

  const nameValue = watch('name');

  // Auto-generate slug from name
  const handleNameBlur = () => {
    const slug = nameValue
      ?.toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
    if (slug) setValue('seo_name', slug, { shouldValidate: true });
  };

  const onSubmit = async (data: FormData) => {
    setApiError(null);
    try {
      const res = await organisationsApi.create({
        ...data,
        website: data.website || undefined,
        phone: data.phone || undefined,
        description: data.description || undefined,
        timezone: data.timezone || undefined,
      });
      router.push(`/admin/organisations/${res.data.data.id}`);
    } catch (err: unknown) {
      const msg =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ||
        'Failed to create organisation.';
      setApiError(msg);
    }
  };

  return (
    <>
      <PageHeader
        title="New organisation"
        description="Create a new organisation on the Membix platform."
        backHref="/admin/organisations"
        backLabel="Organisations"
      />

      <div className="max-w-2xl">
        {apiError && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {apiError}
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="input"
              label="Organisation name"
              required
              id="name"
              placeholder="Riverside Tennis Club"
              error={errors.name?.message}
              {...register('name', { onBlur: handleNameBlur })}
            />
            <FormField
              as="input"
              label="Slug"
              required
              id="seo_name"
              placeholder="riverside-tennis-club"
              hint="Lowercase letters, numbers and hyphens"
              error={errors.seo_name?.message}
              {...register('seo_name')}
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="input"
              type="email"
              label="Email"
              required
              id="email"
              placeholder="hello@example.com"
              error={errors.email?.message}
              {...register('email')}
            />
            <FormField
              as="input"
              type="tel"
              label="Phone"
              id="phone"
              placeholder="+44 20 7183 2021"
              error={errors.phone?.message}
              {...register('phone')}
            />
          </div>

          <FormField
            as="input"
            type="url"
            label="Website"
            id="website"
            placeholder="https://example.com"
            error={errors.website?.message}
            {...register('website')}
          />

          <FormField
            as="textarea"
            label="Description"
            id="description"
            placeholder="Brief description of the organisation…"
            error={errors.description?.message}
            {...register('description')}
          />

          <FormField
            as="input"
            label="Timezone"
            id="timezone"
            placeholder="Europe/London"
            hint="IANA timezone identifier"
            error={errors.timezone?.message}
            {...register('timezone')}
          />

          <div className="flex items-center gap-3">
            <input
              type="checkbox"
              id="is_active"
              className="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              {...register('is_active')}
            />
            <label htmlFor="is_active" className="text-sm font-medium text-gray-700">
              Active
            </label>
          </div>

          <div className="flex items-center gap-3 pt-2 border-t border-gray-100">
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-5 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 transition-colors"
            >
              {isSubmitting ? 'Creating…' : 'Create organisation'}
            </button>
            <button
              type="button"
              onClick={() => router.back()}
              className="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </>
  );
}
