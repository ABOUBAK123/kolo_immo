import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// For Android emulator: 10.0.2.2 maps to host machine localhost
// For iOS simulator: localhost works directly
// For physical device: use your machine's local IP (e.g. 192.168.1.x)
const BASE_URL = 'http://10.0.2.2/kolo_immo/public/api/v1';

export const apiClient = axios.create({
  baseURL: BASE_URL,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Attach token on every request
apiClient.interceptors.request.use(async config => {
  const token = await AsyncStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Global error handling
apiClient.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      await AsyncStorage.removeItem('auth_token');
      await AsyncStorage.removeItem('auth_user');
    }
    return Promise.reject(error);
  },
);

export default apiClient;
