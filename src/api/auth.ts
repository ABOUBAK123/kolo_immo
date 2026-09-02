import apiClient from './client';
import {User} from '../types';

export interface LoginPayload {
  email?: string;
  phone?: string;
  password: string;
}

export interface RegisterPayload {
  name: string;
  email?: string;
  phone?: string;
  password: string;
  role: 'tenant' | 'owner' | 'both' | 'agent';
  country?: string;
  city?: string;
  /** Optional referral code shared by an agent (ignored silently if invalid). */
  agent_code?: string;
}

// Enveloppe standard de l'API: { success, message, data: {...} }
interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

interface AuthData {
  token: string;
  user: User;
}

interface RegisterData {
  user_id: number;
  phone: string | null;
  email: string | null;
  needs_activation: boolean;
}

interface ForgotPasswordData {
  phone: string;
  masked: string;
  via: string;
}

export const authApi = {
  login: (payload: LoginPayload) =>
    apiClient.post<ApiResponse<AuthData>>('/auth/login', payload),

  register: (payload: RegisterPayload | FormData) =>
    apiClient.post<ApiResponse<RegisterData>>('/auth/register', payload, {
      headers: payload instanceof FormData ? {'Content-Type': 'multipart/form-data'} : undefined,
    }),

  verifyOtp: (phone: string, code: string) =>
    apiClient.post<ApiResponse<AuthData>>('/auth/verify-otp', {phone, code, purpose: 'phone_verify'}),

  resendOtp: (phone: string, purpose: string = 'phone_verify') =>
    apiClient.post('/auth/resend-otp', {phone, purpose}),

  forgotPassword: (contact: string) =>
    apiClient.post<ApiResponse<ForgotPasswordData>>('/auth/forgot-password', {contact}),

  resetPassword: (phone: string, code: string, password: string) =>
    apiClient.post<ApiResponse<AuthData>>('/auth/reset-password', {
      phone,
      code,
      password,
      password_confirmation: password,
    }),

  logout: () => apiClient.post('/auth/logout'),

  me: () => apiClient.get<ApiResponse<User>>('/auth/me'),

  updateProfile: (data: Partial<User> & {password?: string}) =>
    apiClient.post<ApiResponse<User>>('/auth/profile', data),
};
