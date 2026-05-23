import React from 'react';
import {
  Platform,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {
  BottomTabBarProps,
  createBottomTabNavigator,
} from '@react-navigation/bottom-tabs';
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
import {OwnerPropertiesScreen} from '../screens/owner/OwnerPropertiesScreen';
import {OwnerAddPropertyScreen} from '../screens/owner/OwnerAddPropertyScreen';
import {PaymentScreen} from '../screens/bookings/PaymentScreen';
import {colors, radius, shadows} from '../utils/theme';
import {
  HomeStackParamList,
  SearchStackParamList,
  BookingsStackParamList,
  MessagesStackParamList,
  ProfileStackParamList,
} from '../types';

const Tab = createBottomTabNavigator();

const HomeStack    = createNativeStackNavigator<HomeStackParamList>();
const SearchStack  = createNativeStackNavigator<SearchStackParamList>();
const BookingsStack = createNativeStackNavigator<BookingsStackParamList>();
const MessagesStack = createNativeStackNavigator<MessagesStackParamList>();
const ProfileStack  = createNativeStackNavigator<ProfileStackParamList>();

const TAB_ITEMS = [
  {name: 'Home',     label: 'Accueil',   emoji: '🏠'},
  {name: 'Search',   label: 'Chercher',  emoji: '🔍'},
  {name: 'Bookings', label: 'Séjours',   emoji: '📅'},
  {name: 'Messages', label: 'Messages',  emoji: '💬'},
  {name: 'Profile',  label: 'Profil',    emoji: '👤'},
];

function CustomTabBar({state, navigation}: BottomTabBarProps) {
  return (
    <View style={styles.tabBarOuter}>
      <View style={styles.tabBar}>
        {state.routes.map((route, index) => {
          const item    = TAB_ITEMS.find(t => t.name === route.name) ?? TAB_ITEMS[0];
          const focused = state.index === index;

          const onPress = () => {
            const event = navigation.emit({type: 'tabPress', target: route.key, canPreventDefault: true});
            if (!focused && !event.defaultPrevented) {
              navigation.navigate(route.name);
            }
          };

          return (
            <TouchableOpacity
              key={route.key}
              style={styles.tabItem}
              onPress={onPress}
              activeOpacity={0.7}>
              <View style={[styles.iconWrap, focused && styles.iconWrapActive]}>
                <Text style={[styles.tabEmoji, focused && styles.tabEmojiActive]}>
                  {item.emoji}
                </Text>
              </View>
              <Text style={[styles.tabLabel, focused && styles.tabLabelActive]}>
                {item.label}
              </Text>
              {focused && <View style={styles.activeDot} />}
            </TouchableOpacity>
          );
        })}
      </View>
    </View>
  );
}

function HomeStackNav() {
  return (
    <HomeStack.Navigator screenOptions={{headerShown: false}}>
      <HomeStack.Screen name="HomeScreen"      component={HomeScreen} />
      <HomeStack.Screen name="PropertyDetail"  component={PropertyDetailScreen} />
      <HomeStack.Screen name="BookingCreate"   component={BookingCreateScreen} />
      <HomeStack.Screen name="BookingDetail"   component={BookingDetailScreen} />
      <HomeStack.Screen name="Payment"         component={PaymentScreen} />
    </HomeStack.Navigator>
  );
}

function SearchStackNav() {
  return (
    <SearchStack.Navigator screenOptions={{headerShown: false}}>
      <SearchStack.Screen name="SearchScreen"    component={SearchScreen} />
      <SearchStack.Screen name="PropertyDetail"  component={PropertyDetailScreen} />
      <SearchStack.Screen name="BookingCreate"   component={BookingCreateScreen} />
    </SearchStack.Navigator>
  );
}

function BookingsStackNav() {
  return (
    <BookingsStack.Navigator screenOptions={{headerShown: false}}>
      <BookingsStack.Screen name="BookingList"   component={BookingListScreen} />
      <BookingsStack.Screen name="BookingDetail" component={BookingDetailScreen} />
      <BookingsStack.Screen name="Payment"       component={PaymentScreen} />
    </BookingsStack.Navigator>
  );
}

function MessagesStackNav() {
  return (
    <MessagesStack.Navigator screenOptions={{headerShown: false}}>
      <MessagesStack.Screen name="ConversationList" component={ConversationListScreen} />
      <MessagesStack.Screen name="Conversation"     component={ConversationScreen} />
    </MessagesStack.Navigator>
  );
}

function ProfileStackNav() {
  return (
    <ProfileStack.Navigator screenOptions={{headerShown: false}}>
      <ProfileStack.Screen name="ProfileScreen"    component={ProfileScreen} />
      <ProfileStack.Screen name="OwnerProperties"  component={OwnerPropertiesScreen} />
      <ProfileStack.Screen name="OwnerAddProperty" component={OwnerAddPropertyScreen} />
    </ProfileStack.Navigator>
  );
}

export const MainNavigator: React.FC = () => (
  <Tab.Navigator
    tabBar={props => <CustomTabBar {...props} />}
    screenOptions={{headerShown: false}}>
    <Tab.Screen name="Home"     component={HomeStackNav} />
    <Tab.Screen name="Search"   component={SearchStackNav} />
    <Tab.Screen name="Bookings" component={BookingsStackNav} />
    <Tab.Screen name="Messages" component={MessagesStackNav} />
    <Tab.Screen name="Profile"  component={ProfileStackNav} />
  </Tab.Navigator>
);

const styles = StyleSheet.create({
  tabBarOuter: {
    backgroundColor: 'transparent',
    paddingHorizontal: 16,
    paddingBottom: Platform.OS === 'ios' ? 24 : 10,
    paddingTop: 8,
  },
  tabBar: {
    flexDirection: 'row',
    backgroundColor: colors.surface,
    borderRadius: radius.xxl,
    paddingVertical: 8,
    paddingHorizontal: 6,
    ...shadows.lg,
  },
  tabItem: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 3,
    position: 'relative',
    paddingVertical: 4,
  },
  iconWrap: {
    width: 44,
    height: 36,
    borderRadius: radius.lg,
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconWrapActive: {
    backgroundColor: colors.primaryFaint,
  },
  tabEmoji: {
    fontSize: 20,
    opacity: 0.45,
  },
  tabEmojiActive: {
    opacity: 1,
  },
  tabLabel: {
    fontSize: 10,
    fontWeight: '500',
    color: colors.textTertiary,
    letterSpacing: 0.2,
  },
  tabLabelActive: {
    color: colors.primary,
    fontWeight: '700',
  },
  activeDot: {
    position: 'absolute',
    bottom: -2,
    width: 4,
    height: 4,
    borderRadius: 2,
    backgroundColor: colors.primary,
  },
});
