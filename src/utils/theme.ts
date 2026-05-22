export const colors = {
  primary: '#1B4F72',
  primaryLight: '#2E86AB',
  primaryDark: '#154360',
  secondary: '#F39C12',
  success: '#27AE60',
  danger: '#E74C3C',
  warning: '#F39C12',
  info: '#3498DB',

  background: '#F8F9FA',
  surface: '#FFFFFF',
  border: '#E8ECF0',
  borderLight: '#F0F2F5',

  text: '#1A202C',
  textSecondary: '#718096',
  textLight: '#A0AEC0',
  textOnPrimary: '#FFFFFF',

  gray50: '#F9FAFB',
  gray100: '#F3F4F6',
  gray200: '#E5E7EB',
  gray300: '#D1D5DB',
  gray400: '#9CA3AF',
  gray500: '#6B7280',

  statusPending: '#F59E0B',
  statusConfirmed: '#10B981',
  statusCancelled: '#EF4444',
  statusCompleted: '#3B82F6',
};

export const spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
};

export const typography = {
  h1: {fontSize: 28, fontWeight: '700' as const, lineHeight: 36},
  h2: {fontSize: 22, fontWeight: '700' as const, lineHeight: 30},
  h3: {fontSize: 18, fontWeight: '600' as const, lineHeight: 26},
  body: {fontSize: 14, fontWeight: '400' as const, lineHeight: 22},
  bodyMd: {fontSize: 15, fontWeight: '400' as const, lineHeight: 23},
  caption: {fontSize: 12, fontWeight: '400' as const, lineHeight: 18},
  label: {fontSize: 13, fontWeight: '500' as const, lineHeight: 20},
  button: {fontSize: 15, fontWeight: '600' as const},
};

export const radius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  full: 999,
};

export const shadows = {
  sm: {
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 1},
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 2,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.08,
    shadowRadius: 6,
    elevation: 4,
  },
};
