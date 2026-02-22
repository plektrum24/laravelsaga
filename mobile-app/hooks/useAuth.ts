import { useEffect, useCallback } from 'react';
import { useAuthStore } from '../stores/auth.store';

export const useAuth = () => {
  const { 
    user, 
    isAuthenticated, 
    isLoading, 
    error,
    login, 
    register, 
    logout, 
    loadUser,
    updateUser,
    clearError 
  } = useAuthStore();

  // Load user on mount if token exists
  useEffect(() => {
    loadUser();
  }, []);

  const handleLogin = useCallback(async (email: string, password: string) => {
    await login(email, password);
  }, [login]);

  const handleRegister = useCallback(async (name: string, email: string, password: string) => {
    await register(name, email, password);
  }, [register]);

  const handleLogout = useCallback(async () => {
    await logout();
  }, [logout]);

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    login: handleLogin,
    register: handleRegister,
    logout: handleLogout,
    updateUser,
    clearError,
  };
};

export default useAuth;
