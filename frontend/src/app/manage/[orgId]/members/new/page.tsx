'use client';

import { useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { membersApi } from '@/lib/api/members';
import PageHeader from '@/components/ui/PageHeader';
import FormField from '@/components/ui/FormField';

const schema = z.object({
  first_name: z.string().min(1, 'First name is required'),
  last_name: z.string().min(1, 'Last name is required'),
  email: z.string().email('Invalid email'),
  title: z.string().optional(),
  mobile_phone: z.string().optional(),
  date_of_birth: z.string().optional(),
  gender: z.enum(['female', 'male', 'other', 'prefer_not_to_say']).optional(),
  member_number: z.string().optional(),
  classification: z.string().optional(),
  joined_at: z.string().optional(),
  is_active: z.boolean(),
});

type FormData = z.infer<typeof schema>;

export default function ManageNewMemberPage() {
  const { orgId } = useParams<{ orgId: string }>();
  const router = useRouter();
  const [apiError, setApiError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { is_active: true },
  });

  const onSubmit = async (data: FormData) => {
    setApiError(null);
    try {
      await membersApi.create(orgId, {
        ...data,
        title: data.title || undefined,
        mobile_phone: data.mobile_phone || undefined,
        date_of_birth: data.date_of_birth || undefined,
        gender: data.gender || undefined,
        member_number: data.member_number || undefined,
        classification: data.classification || undefined,
        joined_at: data.joined_at || undefined,
      });
      router.push(`/manage/${orgId}/members`);
    } catch (err: unknown) {
      const msg =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ||
        'Failed to create member.';
      setApiError(msg);
    }
  };

  return (
    <>
      <PageHeader
        title="Add member"
        description="Add a new member to your organisation."
        backHref={`/manage/${orgId}/members`}
        backLabel="Members"
      />

      <div className="max-w-2xl">
        {apiError && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {apiError}
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
          {/* Name */}
          <div className="grid grid-cols-3 gap-4">
            <FormField
              as="input"
              label="Title"
              id="title"
              placeholder="Mr / Ms"
              error={errors.title?.message}
              {...register('title')}
            />
            <FormField
              as="input"
              label="First name"
              required
              id="first_name"
              placeholder="Alice"
              error={errors.first_name?.message}
              {...register('first_name')}
            />
            <FormField
              as="input"
              label="Last name"
              required
              id="last_name"
              placeholder="Smith"
              error={errors.last_name?.message}
              {...register('last_name')}
            />
          </div>

          {/* Contact */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="input"
              type="email"
              label="Email"
              required
              id="email"
              placeholder="alice@example.com"
              error={errors.email?.message}
              {...register('email')}
            />
            <FormField
              as="input"
              label="Mobile phone"
              id="mobile_phone"
              placeholder="+44 7700 900000"
              error={errors.mobile_phone?.message}
              {...register('mobile_phone')}
            />
          </div>

          {/* Demographics */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="input"
              type="date"
              label="Date of birth"
              id="date_of_birth"
              error={errors.date_of_birth?.message}
              {...register('date_of_birth')}
            />
            <FormField
              as="select"
              label="Gender"
              id="gender"
              error={errors.gender?.message}
              {...register('gender')}
            >
              <option value="">— Select —</option>
              <option value="female">Female</option>
              <option value="male">Male</option>
              <option value="other">Other</option>
              <option value="prefer_not_to_say">Prefer not to say</option>
            </FormField>
          </div>

          {/* Membership */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <FormField
              as="input"
              label="Member number"
              id="member_number"
              placeholder="MBR-001"
              error={errors.member_number?.message}
              {...register('member_number')}
            />
            <FormField
              as="input"
              label="Classification"
              id="classification"
              placeholder="Adult / Junior / Senior"
              error={errors.classification?.message}
              {...register('classification')}
            />
          </div>

          <FormField
            as="input"
            type="date"
            label="Joined at"
            id="joined_at"
            error={errors.joined_at?.message}
            {...register('joined_at')}
          />

          {/* Active */}
          <div className="flex items-center gap-3">
            <input
              type="checkbox"
              id="is_active"
              className="w-4 h-4 rounded border-gray-300 text-emerald-600"
              {...register('is_active')}
            />
            <label htmlFor="is_active" className="text-sm text-gray-700 font-medium">
              Active member
            </label>
          </div>

          <div className="flex items-center gap-3 pt-2">
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-5 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {isSubmitting ? 'Adding…' : 'Add member'}
            </button>
            <button
              type="button"
              onClick={() => router.push(`/manage/${orgId}/members`)}
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
