export const colors = {
  // Brand
  primary: '#1B4F72',
  primaryDark: '#154360',
  primaryMid: '#2471A3',
  primaryLight: '#D6EAF8',
  primaryFaint: '#EBF5FB',

  accent: '#E67E22',
  accentLight: '#FDEBD0',
  accentFaint: '#FEF9F0',

  // Semantic
  success: '#16A34A',
  successLight: '#DCFCE7',
  danger: '#DC2626',
  dangerLight: '#FEE2E2',
  warning: '#D97706',
  warningLight: '#FEF3C7',
  info: '#0284C7',
  infoLight: '#E0F2FE',

  // Layout
  background: '#F4F7FB',
  surface: '#FFFFFF',
  surfaceSecondary: '#F8FAFB',
  overlay: 'rgba(0,0,0,0.45)',

  // Text
  text: '#111827',
  textMed: '#374151',
  textSecondary: '#6B7280',
  textTertiary: '#9CA3AF',
  textInverse: '#FFFFFF',

  // Borders
  border: '#E5E9F0',
  borderLight: '#F1F4F8',
  divider: '#F0F2F5',

  // Neutral scale
  gray50:  '#F9FAFB',
  gray100: '#F3F4F6',
  gray200: '#E5E7EB',
  gray300: '#D1D5DB',
  gray400: '#9CA3AF',
  gray500: '#6B7280',
  gray600: '#4B5563',
  gray700: '#374151',
  gray800: '#1F2937',
  gray900: '#111827',

  // Status (booking)
  pending:   '#D97706',
  confirmed: '#16A34A',
  cancelled: '#DC2626',
  completed: '#1D4ED8',
  disputed:  '#7C3AED',
};

export const spacing = {
  xs:  4,
  sm:  8,
  md:  16,
  lg:  24,
  xl:  32,
  xxl: 48,
};

export const radius = {
  xs:   4,
  sm:   8,
  md:   12,
  lg:   16,
  xl:   20,
  xxl:  28,
  full: 999,
};

export const typography = {
  display: { fontSize: 32, fontWeight: '800' as const, lineHeight: 40, letterSpacing: -0.5 },
  h1:      { fontSize: 26, fontWeight: '700' as const, lineHeight: 34, letterSpacing: -0.3 },
  h2:      { fontSize: 22, fontWeight: '700' as const, lineHeight: 30, letterSpacing: -0.2 },
  h3:      { fontSize: 18, fontWeight: '600' as const, lineHeight: 26 },
  h4:      { fontSize: 16, fontWeight: '600' as const, lineHeight: 24 },
  bodyLg:  { fontSize: 16, fontWeight: '400' as const, lineHeight: 26 },
  body:    { fontSize: 14, fontWeight: '400' as const, lineHeight: 22 },
  bodySm:  { fontSize: 13, fontWeight: '400' as const, lineHeight: 20 },
  caption: { fontSize: 12, fontWeight: '400' as const, lineHeight: 18 },
  label:   { fontSize: 13, fontWeight: '500' as const, lineHeight: 20 },
  labelSm: { fontSize: 11, fontWeight: '600' as const, lineHeight: 16, letterSpacing: 0.5 },
  button:  { fontSize: 15, fontWeight: '600' as const, lineHeight: 22 },
  buttonSm:{ fontSize: 13, fontWeight: '600' as const, lineHeight: 20 },
};

export const shadows = {
  xs: {
    shadowColor: '#1B4F72',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.06,
    shadowRadius: 2,
    elevation: 1,
  },
  sm: {
    shadowColor: '#1B4F72',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 6,
    elevation: 2,
  },
  md: {
    shadowColor: '#1B4F72',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.10,
    shadowRadius: 12,
    elevation: 4,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.12,
    shadowRadius: 20,
    elevation: 8,
  },
};
