// Generic API response envelope
export interface ApiResponse<T = unknown> {
  data: T;
  message?: string;
}

export interface PaginatedResponse<T = unknown> {
  data: T[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
}

// Auth
export interface User {
  id: string;
  email: string;
  is_admin: boolean;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

// Organisation
export interface Organisation {
  id: string;
  name: string;
  seo_name: string;
  email: string;
  phone: string | null;
  logo: string | null;
  website: string | null;
  description: string | null;
  timezone: string | null;
  free_trail: boolean;
  free_trail_end_date: string | null;
  is_active: boolean;
  members_count?: number;
  created_at: string;
  updated_at: string;
}

export interface StoreOrganisationPayload {
  name: string;
  seo_name: string;
  email: string;
  phone?: string;
  website?: string;
  description?: string;
  timezone?: string;
  is_active?: boolean;
}

export interface UpdateOrganisationPayload extends Partial<StoreOrganisationPayload> {}

// Member
export interface Member {
  id: string;
  user_id: string | null;
  organisation_id: string;
  full_name: string;
  title: string | null;
  first_name: string;
  last_name: string;
  email: string;
  mobile_phone: string | null;
  date_of_birth: string | null;
  gender: 'female' | 'male' | 'other' | 'prefer_not_to_say' | null;
  member_number: string | null;
  classification: string | null;
  joined_at: string | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface StoreMemberPayload {
  first_name: string;
  last_name: string;
  email: string;
  title?: string;
  mobile_phone?: string;
  date_of_birth?: string;
  gender?: 'female' | 'male' | 'other' | 'prefer_not_to_say';
  member_number?: string;
  classification?: string;
  joined_at?: string;
  is_active?: boolean;
}

export interface UpdateMemberPayload extends Partial<StoreMemberPayload> {}

// Auth payloads
export interface LoginPayload {
  email: string;
  password: string;
}

export interface RegisterPayload {
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  token: string;
  user: User;
}

// Subscription types
export type SubscriptionPeriod = 'day' | 'week' | 'month' | 'year' | 'lifetime' | 'none' | 'instalments';
export type SubscriptionRenewalType = 'auto_renew' | 'manual' | 'not_renewable';
export type SubscriptionPricingType = 'flat' | 'family' | 'corporate' | 'tiered' | 'custom_variable';
export type SubscriptionPublished = 'published' | 'renewal_only' | 'unpublished';
export type SubscriptionMembershipType = 'individual' | 'group';

export interface Subscription {
  id: string;
  organisation_id: string;
  name: string;
  description: string | null;
  membership_type: SubscriptionMembershipType;
  period: SubscriptionPeriod;
  renewal_type: SubscriptionRenewalType;
  pricing_type: SubscriptionPricingType;
  published: SubscriptionPublished;
  is_joining_fee: boolean;
  price_options?: SubscriptionPriceOption[];
  price_options_count?: number;
  created_at: string;
  updated_at: string;
}

export interface StoreSubscriptionPayload {
  name: string;
  description?: string;
  membership_type?: SubscriptionMembershipType;
  period?: SubscriptionPeriod;
  renewal_type?: SubscriptionRenewalType;
  pricing_type?: SubscriptionPricingType;
  published?: SubscriptionPublished;
  is_joining_fee?: boolean;
}

export interface SubscriptionPriceOption {
  id: string;
  subscription_id: string;
  name: string;
  eligibility: 'individual' | 'adult' | 'junior' | 'family' | 'corporate';
  pricing_type: 'flat' | 'tiered' | 'custom_variable';
  price: number;
  price_min: number | null;
  price_max: number | null;
  published: 'published' | 'renewals_only' | 'unpublished';
  created_at: string;
  updated_at: string;
}

export interface StoreSubscriptionPriceOptionPayload {
  name: string;
  eligibility?: SubscriptionPriceOption['eligibility'];
  pricing_type?: SubscriptionPriceOption['pricing_type'];
  price: number;
  price_min?: number;
  price_max?: number;
  published?: SubscriptionPriceOption['published'];
}

export interface SubscriptionStatsRow {
  id: string;
  name: string;
  membership_type: string;
  period: string;
  published: string;
  active_count: number;
  members_count: number;
  auto_renew: number;
  manual_renew: number;
  not_renewable: number;
  recently_expired: number;
}

export interface SubscriptionStats {
  summary: {
    membership_subscriptions: number;
    other_subscriptions: number;
    members: number;
    expired_last_30_days: number;
    pending_payment: number;
  };
  breakdown: SubscriptionStatsRow[];
}

// Order types
export type OrderStatus =
  | 'pending'
  | 'payment_received'
  | 'payment_problem'
  | 'cancelled'
  | 'no_payment_required'
  | 'completed'
  | 'partial_payment'
  | 'refunded';

export interface Order {
  id: string;
  member_id: string | null;
  organisation_id: string;
  name: string;
  email: string;
  payment_method_id: string | null;
  payment_reference: string | null;
  status: OrderStatus;
  date_placed: string;
  date_finished: string | null;
  comments: string | null;
  currency_code: string;
  tax_total: string;
  total: string;
  items?: OrderItem[];
  created_at: string;
  updated_at: string;
}

export interface OrderItem {
  id: string;
  order_id: string;
  item_type: string;
  item_id: string | null;
  description: string;
  quantity: number;
  unit_price: string;
  tax_rate: string | null;
  total: string;
}
