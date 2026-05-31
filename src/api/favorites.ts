import apiClient from './client';

export interface FavoriteProperty {
  id: number;
  title: string;
  city: string;
  price_per_night: number;
  cover_photo_url: string | null;
  rating_avg: number;
  type: string;
  price_at_save: number | null;
  price_dropped: boolean;
}

interface FavoritesResponse {
  success: boolean;
  data: FavoriteProperty[];
}

interface ToggleResponse {
  success: boolean;
  faved: boolean;
  message: string;
}

export const favoritesApi = {
  list: () =>
    apiClient.get<FavoritesResponse>('/favorites'),

  toggle: (propertyId: number) =>
    apiClient.post<ToggleResponse>(`/favorites/${propertyId}/toggle`),
};
