<aside x-cloak x-transition:enter="transition transform duration-300"
       x-transition:enter-start="-translate-x-full opacity-30  ease-in"
       x-transition:enter-end="translate-x-0 opacity-100 ease-out"
       x-transition:leave="transition transform duration-300"
       x-transition:leave-start="translate-x-0 opacity-100 ease-out"
       x-transition:leave-end="-translate-x-full opacity-0 ease-in"
       class="fixed inset-y-0 z-10 flex flex-col flex-shrink-0 w-64 max-h-screen  transition-all transform bg-white border-r shadow-lg lg:z-auto lg:static lg:shadow-none"
       :class="{ '-translate-x-full lg:translate-x-0 lg:w-20': !isSidebarOpen }">
    <!-- sidebar header -->
    <div class="flex items-center justify-between flex-shrink-0 p-2" :class="{ 'lg:justify-center': !isSidebarOpen }">
        <x-application-logo class="mx-auto h-4 w-auto"/>

        <button @click="toggleSidbarMenu()" class="p-2 rounded-md lg:hidden">
            <svg class="w-6 h-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>

        </button>
    </div>
    <!-- Sidebar links -->
    <nav class="flex-1 hover:overflow-y-auto">
        <ul class="p-2  space-y-2">

            {{-- Dashboard --}}
            <x-backend.menu-item title='Dashboard' :hasSubMenu="false" route="admin.dashboard"
                                 is-active="{{ request()->is('admin/dashboard') }}">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20.828" height="20" viewBox="0 0 20.828 20">
                        <path id="Path_1" data-name="Path 1"
                              d="M3,12l2-2m0,0,7-7,7,7M5,10V20a1,1,0,0,0,1,1H9M19,10l2,2m-2-2V20a1,1,0,0,1-1,1H15M9,21a1,1,0,0,0,1-1V16a1,1,0,0,1,1-1h2a1,1,0,0,1,1,1v4a1,1,0,0,0,1,1M9,21h6"
                              transform="translate(-1.586 -2)" fill="none"
                              stroke="{{ request()->is('admin/dashboard') ? '#020157' : '#505050' }}"
                              stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2" opacity="1"/>
                    </svg>
                </x-slot:icon>
            </x-backend.menu-item>

            @if((auth()->user()->can(\App\Enums\PermissionsEnum::VIEW_USER_MANAGEMENT->value)) || (auth()->user()->can(\App\Enums\PermissionsEnum::VIEW_ROLE_MANAGEMENT->value)))
                <x-backend.menu-item title='Admin User Management' :hasSubMenu="true"
                                     is-active="{{ request()->is('admin/user-management*') }}">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 18 20">
                            <path id="Path_2" data-name="Path 2"
                                  d="M17.571,7A4.314,4.314,0,0,1,13,11,4.314,4.314,0,0,1,8.429,7,4.314,4.314,0,0,1,13,3a4.314,4.314,0,0,1,4.571,4ZM13,14c-4.418,0-8,3.134-8,7H21C21,17.134,17.418,14,13,14Z"
                                  transform="translate(-4 -2)" fill="none"
                                  stroke="{{ request()->is('admin/user-management*') ? '#020157' : '#505050' }}"
                                  stroke-linecap="round"
                                  stroke-linejoin="round" stroke-width="2" opacity="1"/>
                        </svg>
                    </x-slot:icon>

                    @if(auth()->user()->can(\App\Enums\PermissionsEnum::VIEW_USER_MANAGEMENT->value))
                        <a href="{{ route('admin.user-management.users') }}" role="menuitem"
                           class="block p-1 text-xs  {{ request()->is('admin/user-management/users') ? 'text-gray-800 font-bold' : 'text-gray-400' }} transition-colors duration-200 rounded-md dark:text-gray-400 dark:hover:text-light hover:text-gray-700 {{ request()->is('admin/user-management/users') ? 'active-menu' : '' }}"
                           :class="{ 'lg:hidden': !isSidebarOpen }">
                            Manage Users
                        </a>
                    @endif
                    @if(auth()->user()->can(\App\Enums\PermissionsEnum::VIEW_ROLE_MANAGEMENT->value))
                        <a href="{{ route('admin.user-management.roles') }}" role="menuitem"
                           class="block p-1 text-xs  {{ request()->is('admin/user-management/roles') ? 'text-gray-800 font-bold' : 'text-gray-400' }} transition-colors duration-200 rounded-md dark:text-gray-400 dark:hover:text-light hover:text-gray-700 {{ request()->is('admin/user-management/roles') ? 'active-menu' : '' }}"
                           :class="{ 'lg:hidden': !isSidebarOpen }">
                            Manage Roles
                        </a>
                    @endif
                </x-backend.menu-item>
            @endif


            @if((auth()->user()->can(\App\Enums\PermissionsEnum::VIEW_ACTIVITY_LOGS->value)))
                <x-backend.menu-item title='Portal Management' :hasSubMenu="true"
                                     is-active="{{ request()->is('admin/portal-management*') }}">
                    <x-slot:icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                            <path id="Path_12" data-name="Path 12"
                                  d="M18.625,15.25V22M15.25,18.625H22M6.25,10.75H8.5A2.25,2.25,0,0,0,10.75,8.5V6.25A2.25,2.25,0,0,0,8.5,4H6.25A2.25,2.25,0,0,0,4,6.25V8.5a2.25,2.25,0,0,0,2.25,2.25Zm11.25,0h2.25A2.25,2.25,0,0,0,22,8.5V6.25A2.25,2.25,0,0,0,19.75,4H17.5a2.25,2.25,0,0,0-2.25,2.25V8.5A2.25,2.25,0,0,0,17.5,10.75ZM6.25,22H8.5a2.25,2.25,0,0,0,2.25-2.25V17.5A2.25,2.25,0,0,0,8.5,15.25H6.25A2.25,2.25,0,0,0,4,17.5v2.25A2.25,2.25,0,0,0,6.25,22Z" transform="translate(-3 -3)" fill="none"
                                  stroke="{{ request()->is('admin/portal-management*') ? '#020157' : '#505050' }}" stroke-linecap="round"
                                  stroke-linejoin="round" stroke-width="2" opacity="1"></path>
                        </svg>
                    </x-slot:icon>

                    @if(auth()->user()->can(\App\Enums\PermissionsEnum::VIEW_ACTIVITY_LOGS->value))
                        <a href="{{ route('admin.portal-management.activity-logs') }}" role="menuitem"
                           class="block p-1 text-xs  {{ request()->is('admin/portal-management/activity-logs') ? 'text-gray-800 font-bold' : 'text-gray-400' }} transition-colors duration-200 rounded-md dark:text-gray-400 dark:hover:text-light hover:text-gray-700 {{ request()->is('admin/portal-management/activity-logs') ? 'active-menu' : '' }}"
                           :class="{ 'lg:hidden': !isSidebarOpen }">
                            Activity Logs
                        </a>
                    @endif
                </x-backend.menu-item>
            @endif

            <!-- Service Provider Management -->
            <x-backend.menu-item title='Biller Management' :hasSubMenu="true"
                                 is-active="{{ request()->is('admin/providers*') }}">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                        <path id="Path_6" data-name="Path 6"
                              d="M7,21a4,4,0,0,1-4-4V5A2,2,0,0,1,5,3H9a2,2,0,0,1,2,2V17A4,4,0,0,1,7,21Zm0,0H19a2,2,0,0,0,2-2V15a2,2,0,0,0-2-2H16.657M11,7.343l1.657-1.657a2,2,0,0,1,2.828,0l2.829,2.829a2,2,0,0,1,0,2.828L9.828,19.828M7,17h.01"
                              transform="translate(-2 -2)" fill="none" stroke="#000" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2" opacity="0.631"/>
                    </svg>
                </x-slot:icon>

                <a href="{{ route('admin.providers.category') }}" role="menuitem"
                   class="block p-1 text-xs text-gray-400 transition-colors duration-200 rounded-md dark:text-gray-400 dark:hover:text-light hover:text-gray-700 {{ request()->is('admin/providers/category') ? 'active-menu' : '' }}"
                   :class="{ 'lg:hidden': !isSidebarOpen }">
                    Provider Categories
                </a>

                <a href="{{ route('admin.providers.biller') }}" role="menuitem"
                   class="block p-1 text-xs text-gray-400 transition-colors duration-200 rounded-md dark:text-gray-400 dark:hover:text-light hover:text-gray-700 {{ request()->is('admin/providers/biller') ? 'active-menu' : '' }}"
                   :class="{ 'lg:hidden': !isSidebarOpen }">
                    Providers
                </a>

            </x-backend.menu-item>

            <div style="margin-bottom: 2rem;"></div>
        </ul>
    </nav>

</aside>
