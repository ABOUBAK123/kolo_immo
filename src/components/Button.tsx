import React from 'react';
import {
  ActivityIndicator,
  StyleSheet,
  Text,
  TouchableOpacity,
  TouchableOpacityProps,
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
}

export const Button: React.FC<ButtonProps> = ({
  title,
  variant = 'primary',
  size = 'md',
  loading = false,
  fullWidth = false,
  style,
  disabled,
  ...props
}) => {
  const variantStyles = {
    primary: {bg: colors.primary, text: '#fff', border: colors.primary},
    secondary: {bg: colors.secondary, text: '#fff', border: colors.secondary},
    outline: {bg: 'transparent', text: colors.primary, border: colors.primary},
    danger: {bg: colors.danger, text: '#fff', border: colors.danger},
    ghost: {bg: 'transparent', text: colors.primary, border: 'transparent'},
  }[variant];

  const sizeStyles = {
    sm: {paddingVertical: 8, paddingHorizontal: 16, fontSize: 13},
    md: {paddingVertical: 13, paddingHorizontal: 24, fontSize: 15},
    lg: {paddingVertical: 16, paddingHorizontal: 32, fontSize: 16},
  }[size];

  return (
    <TouchableOpacity
      style={[
        styles.base,
        {
          backgroundColor: variantStyles.bg,
          borderColor: variantStyles.border,
          paddingVertical: sizeStyles.paddingVertical,
          paddingHorizontal: sizeStyles.paddingHorizontal,
          width: fullWidth ? '100%' : undefined,
          opacity: disabled || loading ? 0.6 : 1,
        },
        style,
      ]}
      disabled={disabled || loading}
      activeOpacity={0.75}
      {...props}>
      {loading ? (
        <ActivityIndicator color={variantStyles.text} size="small" />
      ) : (
        <Text
          style={[
            typography.button,
            {color: variantStyles.text, fontSize: sizeStyles.fontSize},
          ]}>
          {title}
        </Text>
      )}
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  base: {
    borderRadius: radius.lg,
    borderWidth: 1.5,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
  },
});
