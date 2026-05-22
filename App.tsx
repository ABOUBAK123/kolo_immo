import React from 'react';
import {StatusBar} from 'react-native';
import {AuthProvider} from './src/store/AuthContext';
import {AppNavigator} from './src/navigation/AppNavigator';

function App(): React.JSX.Element {
  return (
    <AuthProvider>
      <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />
      <AppNavigator />
    </AuthProvider>
  );
}

export default App;
