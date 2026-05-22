import React, {createContext, useContext, useEffect, useReducer} from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {User} from '../types';

interface AuthState {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  isAuthenticated: boolean;
}

type AuthAction =
  | {type: 'RESTORE'; user: User | null; token: string | null}
  | {type: 'LOGIN'; user: User; token: string}
  | {type: 'UPDATE_USER'; user: User}
  | {type: 'LOGOUT'};

const initialState: AuthState = {
  user: null,
  token: null,
  isLoading: true,
  isAuthenticated: false,
};

function authReducer(state: AuthState, action: AuthAction): AuthState {
  switch (action.type) {
    case 'RESTORE':
      return {
        ...state,
        user: action.user,
        token: action.token,
        isLoading: false,
        isAuthenticated: !!action.token,
      };
    case 'LOGIN':
      return {
        ...state,
        user: action.user,
        token: action.token,
        isLoading: false,
        isAuthenticated: true,
      };
    case 'UPDATE_USER':
      return {...state, user: action.user};
    case 'LOGOUT':
      return {...state, user: null, token: null, isAuthenticated: false};
    default:
      return state;
  }
}

interface AuthContextType extends AuthState {
  login: (user: User, token: string) => Promise<void>;
  logout: () => Promise<void>;
  updateUser: (user: User) => void;
}

const AuthContext = createContext<AuthContextType>({} as AuthContextType);

export const AuthProvider: React.FC<{children: React.ReactNode}> = ({
  children,
}) => {
  const [state, dispatch] = useReducer(authReducer, initialState);

  useEffect(() => {
    const restore = async () => {
      try {
        const token = await AsyncStorage.getItem('auth_token');
        const userJson = await AsyncStorage.getItem('auth_user');
        const user = userJson ? JSON.parse(userJson) : null;
        dispatch({type: 'RESTORE', user, token});
      } catch {
        dispatch({type: 'RESTORE', user: null, token: null});
      }
    };
    restore();
  }, []);

  const login = async (user: User, token: string) => {
    await AsyncStorage.setItem('auth_token', token);
    await AsyncStorage.setItem('auth_user', JSON.stringify(user));
    dispatch({type: 'LOGIN', user, token});
  };

  const logout = async () => {
    await AsyncStorage.removeItem('auth_token');
    await AsyncStorage.removeItem('auth_user');
    dispatch({type: 'LOGOUT'});
  };

  const updateUser = (user: User) => {
    AsyncStorage.setItem('auth_user', JSON.stringify(user));
    dispatch({type: 'UPDATE_USER', user});
  };

  return (
    <AuthContext.Provider value={{...state, login, logout, updateUser}}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
