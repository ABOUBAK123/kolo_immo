import React from 'react';
import {NavigationContainer} from '@react-navigation/native';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import {useAuth} from '../store/AuthContext';
import {AuthNavigator} from './AuthNavigator';
import {MainNavigator} from './MainNavigator';
import {LoadingSpinner} from '../components/LoadingSpinner';
import {RootStackParamList} from '../types';

const Stack = createNativeStackNavigator<RootStackParamList>();

export const AppNavigator: React.FC = () => {
  const {isLoading, isAuthenticated} = useAuth();

  if (isLoading) {
    return <LoadingSpinner fullScreen message="Chargement..." />;
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{headerShown: false}}>
        {isAuthenticated ? (
          <Stack.Screen name="Main" component={MainNavigator} />
        ) : (
          <Stack.Screen name="Auth" component={AuthNavigator} />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
};
