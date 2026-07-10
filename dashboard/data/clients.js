import { reactive } from 'vue';

// Shared dummy store — stands in for the Livewire Client model / events
// (updateClientList) that decouple the table and add-client components.
let nextId = 6;

export const clients = reactive([
    { id: 1, uuid: 'clt-1', name: 'محمد العتيبي', email: 'mohammed@example.com', phone: '0501234567', active: true },
    { id: 2, uuid: 'clt-2', name: 'سارة القحطاني', email: 'sara@example.com', phone: '0559876543', active: true },
    { id: 3, uuid: 'clt-3', name: 'عبدالله الشمري', email: '', phone: '0533211122', active: false },
    { id: 4, uuid: 'clt-4', name: 'نورة الدوسري', email: 'noura@example.com', phone: '0544455667', active: true },
    { id: 5, uuid: 'clt-5', name: 'فيصل الحربي', email: 'faisal@example.com', phone: '', active: false },
]);

export function avatarFor(name) {
    return `https://ui-avatars.com/api/?background=random&name=${encodeURIComponent(name)}`;
}

export function addClient(data) {
    clients.unshift({ id: nextId, uuid: `clt-${nextId}`, active: true, ...data });
    nextId += 1;
}

export function removeClients(ids) {
    const set = new Set(ids);
    for (let i = clients.length - 1; i >= 0; i -= 1) {
        if (set.has(clients[i].id)) {
            clients.splice(i, 1);
        }
    }
}
