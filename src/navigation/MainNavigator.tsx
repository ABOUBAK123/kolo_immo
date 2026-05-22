import React from 'react';
import {StyleSheet, Text, View} from 'react-native';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import {HomeScreen} from '../screens/home/HomeScreen';
import {SearchScreen} from '../screens/properties/SearchScreen';
import {PropertyDetailScreen} from '../screens/properties/PropertyDetailScreen';
import {BookingCreateScreen} from '../screens/bookings/BookingCreateScreen';
import {BookingDetailScreen} from '../screens/bookings/BookingDetailScreen';
import {BookingListScreen} from '../screens/bookings/BookingListScreen';
import {ConversationListScreen} from '../screens/messages/ConversationListScreen';
import {ConversationScreen} from '../screens/messages/ConversationScreen';
import {ProfileScreen} from '../screens/profile/ProfileScreen';
import {PaymentScreen} from '../screens/bookings/PaymentScreen';
import {colors} from '../utils/theme';
import {
  HomeStackParamList,
  SearchStackParamList,
  BookingsStackParamList,
  MessagesStackParamList,
  ProfileStackParamList,
} from '../types';

const Tab = createBottomTabNavigator();

const HomeStack = createNativeStackNavigator<HomeStackParamList>();
const SearchStack = createNativeStackNavigator<SearchStackParamList>();
const BookingsStack = createNativeStackNavigator<BookingsStackParamList>();
const MessagesStack = createNativeStackNavigator<MessagesStackParamList>();
const ProfileStack = createNativeStackNavigator<ProfileStackParamList>();

const tabIcon = (emoji: string, focused: boolean) => (
  <View style={styles.tabIcon}>
    <Text style={[styles.tabEmoji, {opacity: focused ? 1 : 0.5}]}>{emoji}</Text>
  </View>
);

function HomeStackNav() {
  return (
    <HomeStack.Navigator screenOptions={{headerShown: false}}>
      <HomeStack.Screen name="HomeScreen" component={HomeScreen} />
      <HomeStack.Screen name="PropertyDetail" component={PropertyDetailScreen} />
      <HomeStack.Screen name="BookingCreate" component={BookingCreateScreen} />
      <HomeStack.Screen name="BookingDetail" component={BookingDetailScreen} />
      <HomeStack.Screen name="Payment" component={PaymentScreen} />
    </HomeStack.Navigator>
  );
}

function SearchStackNav() {
  return (
    <SearchStack.Navigator screenOptions={{headerShown: false}}>
      <SearchStack.Screen name="SearchScreen" component={SearchScreen} />
      <SearchStack.Screen name="PropertyDetail" component={PropertyDetailScreen} />
      <SearchStack.Screen name="BookingCreate" component={BookingCreateScreen} />
    </SearchStack.Navigator>
  );
}

function BookingsStackNav() {
  return (
    <BookingsStack.Navigator screenOptions={{headerShown: false}}>
      <BookingsStack.Screen name="BookingList" component={BookingListScreen} />
      <BookingsStack.Screen name="BookingDetail" component={BookingDetailScreen} />
      <BookingsStack.Screen name="Payment" component={PaymentScreen} />
    </BookingsStack.Navigator>
  );
}

function MessagesStackNav() {
  return (
    <MessagesStack.Navigator screenOptions={{headerShown: false}}>
      <MessagesStack.Screen name="ConversationList" component={ConversationListScreen} />
      <MessagesStack.Screen name="Conversation" component={ConversationScreen} />
    </MessagesStack.Navigator>
  );
}

function ProfileStackNav() {
  return (
    <ProfileStack.Navigator screenOptions={{headerShown: false}}>
      <ProfileStack.Screen name="ProfileScreen" component={ProfileScreen} />
    </ProfileStack.Navigator>
  );
}

export const MainNavigator: React.FC = () => (
  <Tab.Navigator
    screenOptions={({route}) => ({
      headerShown: false,
      tabBarActiveTintColor: colors.primary,
      tabBarInactiveTintColor: colors.gray400,
      tabBarStyle: styles.tabBar,
      tabBarLabelStyle: styles.tabLabel,
      tabBarIcon: ({focused}) => {
        const icons: Record<string, string> = {
          Home: '🏠',
          Search: '🔍',
          Bookings: '📅',
          Messages: '💬',
          Profile: '👤',
        };
        return tabIcon(icons[route.name] ?? '●', focused);
      },
    })}>
    <Tab.Screen name="Home" component={HomeStackNav} options={{title: 'Accueil'}} />
    <Tab.Screen name="Search" component={SearchStackNav} options={{title: 'Recherche'}} />
    <Tab.Screen name="Bookings" component={BookingsStackNav} options={{title: 'Séjours'}} />
    <Tab.Screen name="Messages" component={MessagesStackNav} options={{title: 'Messages'}} />
    <Tab.Screen name="Profile" component={ProfileStackNav} options={{title: 'Profil'}} />
  </Tab.Navigator>
);

const styles = StyleSheet.create({
  tabBar: {
    backgroundColor: colors.surface,
    borderTopColor: '#E8ECF0',
    borderTopWidth: 1,
    height: 60,
    paddingBottom: 6,
    paddingTop: 6,
  },
  tabLabel: {fontSize: 10, fontWeight: '600'},
  tabIcon: {alignItems: 'center'},
  tabEmoji: {fontSize: 22},
});
