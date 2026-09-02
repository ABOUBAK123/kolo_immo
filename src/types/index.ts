export interface User {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
  role: 'tenant' | 'owner' | 'both' | 'admin' | 'agent';
  city: string | null;
  country: string | null;
  trust_score: number;
  kyc_status: 'pending' | 'verified' | 'rejected';
  avatar: string | null;
  phone_verified_at: string | null;
  created_at: string;
  /** Referral code assigned to agent accounts once activated by an admin (e.g. "AGT-K3F9QX"). */
  agent_code?: string | null;
  is_active?: boolean;
}

export interface PropertyPhoto {
  id: number;
  url: string;
  path?: string;
  is_cover: boolean;
  sort_order?: number;
  caption?: string | null;
}

export interface PropertyAmenity {
  id: number;
  amenity: string;
}

export interface Property {
  id: number;
  owner_id?: number;
  title: string;
  description: string;
  type: string;
  type_label?: string;
  city: string;
  address: string;
  country: string;
  district?: string | null;
  price_per_night: number;
  price_per_week?: number | null;
  price_per_month?: number | null;
  capacity: number;
  bedrooms: number;
  bathrooms: number;
  area_sqm?: number | null;
  booking_type?: string;
  status?: 'active' | 'inactive' | 'draft' | 'suspended';
  rating_avg: number;
  rating_count?: number;
  reviews_count: number;
  views_count: number;
  cover_photo_url: string | null;
  is_featured?: boolean;
  allow_pets: boolean;
  allow_smoking: boolean;
  allow_parties: boolean;
  cancellation_policy: string;
  photos?: PropertyPhoto[];
  amenities?: PropertyAmenity[];
  owner?: User;
}

export interface Payment {
  id: number;
  amount: number;
  status: 'pending' | 'success' | 'failed' | 'refunded';
  type: string;
  paid_at: string | null;
}

export interface Booking {
  id: number;
  property_id: number;
  tenant_id?: number;
  owner_id?: number;
  reference: string;
  check_in: string;
  check_out: string;
  nights: number;
  guests: number;
  price_per_night: number;
  subtotal: number;
  service_fee?: number;
  deposit_amount?: number;
  total_amount: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'disputed';
  status_label?: string;
  payment_status: string;
  special_requests: string | null;
  confirmed_at?: string | null;
  cancelled_at?: string | null;
  cancellation_reason?: string | null;
  property?: Pick<Property, 'id' | 'title' | 'city' | 'cover_photo_url'>;
  payment?: Payment;
  created_at: string;
}

export interface ChatMessage {
  id: number;
  conversation_id: number;
  sender_id: number;
  body: string | null;
  attachment_path: string | null;
  attachment_type: 'none' | 'image' | 'document';
  read_at: string | null;
  created_at: string;
  sender?: User;
}

export interface Conversation {
  id: number;
  tenant_id: number;
  owner_id: number;
  property_id: number;
  last_message_at: string;
  unread_count?: number;
  property?: Property;
  tenant?: User;
  owner?: User;
  last_message?: ChatMessage;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface ApiResponse<T = any> {
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
}

// Navigation types
export type RootStackParamList = {
  Auth: undefined;
  Main: undefined;
};

export type AuthStackParamList = {
  Login: undefined;
  Register: undefined;
  OTP: { phone: string; userId: number };
  ForgotPassword: undefined;
  ForgotPasswordOTP: { phone: string; masked: string; via: string };
  ResetPassword: { phone: string; code: string };
  SocialAuth: { provider: 'google' | 'facebook' | 'github'; providerLabel: string };
};

export type MainTabParamList = {
  Home: undefined;
  Search: undefined;
  Bookings: undefined;
  Messages: undefined;
  Profile: undefined;
};

export type HomeStackParamList = {
  HomeScreen: undefined;
  PropertyDetail: { property: Property };
  BookingCreate: { property: Property; checkIn?: string; checkOut?: string };
  BookingDetail: { bookingId: number };
  Payment: { bookingId: number };
  OwnerDashboard: undefined;
  OwnerPendingBookings: undefined;
};

export type SearchStackParamList = {
  SearchScreen: undefined;
  PropertyDetail: { property: Property };
  BookingCreate: { property: Property; checkIn?: string; checkOut?: string };
};

export type BookingsStackParamList = {
  BookingList: undefined;
  BookingDetail: { bookingId: number };
  Payment: { bookingId: number };
};

export type MessagesStackParamList = {
  ConversationList: undefined;
  Conversation: { conversationId: number; title: string };
};

export type ProfileStackParamList = {
  ProfileScreen: undefined;
  KycScreen: undefined;
  OwnerProperties: undefined;
  OwnerAddProperty: undefined;
  OwnerCommissions: undefined;
  Favorites: undefined;
};
