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

export const authApi = {
  login: (payload: LoginPayload) =>
    apiClient.post<ApiResponse<AuthData>>('/auth/login', payload),

  register: (payload: RegisterPayload) =>
    apiClient.post<ApiResponse<RegisterData>>('/auth/register', payload),

  verifyOtp: (phone: string, code: string) =>
    apiClient.post<ApiResponse<AuthData>>('/auth/verify-otp', {phone, code, purpose: 'phone_verify'}),

  resendOtp: (phone: string) =>
    apiClient.post('/auth/resend-otp', {phone}),

  logout: () => apiClient.post('/auth/logout'),

  me: () => apiClient.get<ApiResponse<User>>('/auth/me'),

  updateProfile: (data: Partial<User> & {password?: string}) =>
    apiClient.post<ApiResponse<User>>('/auth/profile', data),
};
