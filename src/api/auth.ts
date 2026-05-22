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
  role: 'tenant' | 'owner' | 'both';
  country?: string;
  city?: string;
}

export interface AuthResponse {
  token: string;
  user: User;
  message: string;
}

export const authApi = {
  login: (payload: LoginPayload) =>
    apiClient.post<AuthResponse>('/auth/login', payload),

  register: (payload: RegisterPayload) =>
    apiClient.post<AuthResponse>('/auth/register', payload),

  verifyOtp: (phone: string, otp: string, userId: number) =>
    apiClient.post('/auth/verify-otp', {phone, otp, user_id: userId}),

  logout: () => apiClient.post('/auth/logout'),

  me: () => apiClient.get<{user: User}>('/auth/me'),

  updateProfile: (data: Partial<User> & {password?: string}) =>
    apiClient.post('/auth/profile', data),
};
