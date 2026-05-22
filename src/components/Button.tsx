import React from 'react';
import {
  ActivityIndicator,
  StyleSheet,
  Text,
  TouchableOpacity,
  TouchableOpacityProps,
  View,
  ViewStyle,
} from 'react-native';
import {colors, radius, typography} from '../utils/theme';

interface ButtonProps extends TouchableOpacityProps {
  title: string;
  variant?: 'primary' | 'secondary' | 'outline' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
  fullWidth?: boolean;
  style?: ViewStyle;
  icon?: string;
}

export const Button: React.FC<ButtonProps> = ({
  title,
  variant = 'primary',
  size = 'md',
  loading = false,
  fullWidth = false,
  style,
  disabled,
  icon,
  ...props
}) => {
  const v = {
    primary:   { bg: colors.primary,  text: '#fff',           border: colors.primary },
    secondary: { bg: colors.accent,   text: '#fff',           border: colors.accent },
    outline:   { bg: 'transparent',   text: colors.primary,   border: colors.primary },
    danger:    { bg: colors.danger,   text: '#fff',           border: colors.danger },
    ghost:     { bg: colors.primaryFaint, text: colors.primary, border: 'transparent' },
  }[variant];

  const s = {
    sm: { py: 9,  px: 16, fs: 13, br: radius.md },
    md: { py: 14, px: 20, fs: 15, br: radius.lg },
    lg: { py: 17, px: 24, fs: 16, br: radius.xl },
  }[size];

  return (
    <TouchableOpacity
      style={[
        styles.base,
        {
          backgroundColor: v.bg,
          borderColor: v.border,
          paddingVertical: s.py,
          paddingHorizontal: s.px,
          borderRadius: s.br,
          width: fullWidth ? '100%' : undefined,
          opacity: disabled || loading ? 0.55 : 1,
        },
        style,
      ]}
      disabled={disabled || loading}
      activeOpacity={0.78}
      {...props}>
      {loading ? (
        <ActivityIndicator color={v.text} size="small" />
      ) : (
        <View style={styles.inner}>
          {icon ? <Text style={{fontSize: s.fs, color: v.text}}>{icon}</Text> : null}
          <Text style={[typography.button, {color: v.text, fontSize: s.fs}]}>{title}</Text>
        </View>
      )}
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  base: {
    borderWidth: 1.5,
    alignItems: 'center',
    justifyContent: 'center',
  },
  inner: { flexDirection: 'row', alignItems: 'center', gap: 7 },
});
