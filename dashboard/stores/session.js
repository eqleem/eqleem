import { computed, reactive } from 'vue';
import { api, ApiError } from '../lib/api.js';

const state = reactive({
    loaded: false,
    loading: false,
    user: null,
    tenant: null,
    permissions: {
        can_access_dashboard: false,
        can_manage_tenant: false,
    },
    app: {
        name: 'Eqleem',
        home_url: '/',
        logout_url: '/logout',
    },
    error: null,
});

export function useSession() {
    return {
        state,
        user: computed(() => state.user),
        tenant: computed(() => state.tenant),
        permissions: computed(() => state.permissions),
        app: computed(() => state.app),
        loaded: computed(() => state.loaded),
        loading: computed(() => state.loading),
        canAccessDashboard: computed(() => state.permissions.can_access_dashboard),
        canManageTenant: computed(() => state.permissions.can_manage_tenant),
        loadDashboardContext,
        updateUser,
        updateTenant,
    };
}

export async function loadDashboardContext({ force = false } = {}) {
    if (state.loaded && !force) {
        return state;
    }

    if (state.loading) {
        return state;
    }

    state.loading = true;
    state.error = null;

    try {
        const payload = await api('/dashboard/context');
        const data = payload?.data ?? payload;

        state.user = data.user ?? null;
        state.tenant = data.tenant ?? null;
        state.permissions = {
            can_access_dashboard: Boolean(data.permissions?.can_access_dashboard),
            can_manage_tenant: Boolean(data.permissions?.can_manage_tenant),
        };
        state.app = {
            name: data.app?.name ?? state.app.name,
            home_url: data.app?.home_url ?? state.app.home_url,
            logout_url: data.app?.logout_url ?? state.app.logout_url,
        };
        state.loaded = true;
    } catch (error) {
        state.error = error instanceof ApiError ? error.message : 'Failed to load session';

        if (error instanceof ApiError && error.status === 401) {
            window.location.href = '/login';
        }
    } finally {
        state.loading = false;
    }

    return state;
}

export function updateUser(user) {
    state.user = user;
}

export function updateTenant(tenant) {
    state.tenant = tenant;
}
