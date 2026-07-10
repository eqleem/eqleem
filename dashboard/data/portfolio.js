import { reactive } from 'vue';
import { contentTypeBySlug } from './page.js';

export const portfolioType = contentTypeBySlug('portfolio');

export const projects = reactive([
    {
        id: 1,
        uuid: 'pf-villa-najd',
        title: 'فيلا نجدية معاصرة',
        subtitle: 'تصميم داخلي لفيلا سكنية في الرياض',
        body: '<p>مشروع تصميم داخلي يجمع بين الطابع النجدي المعاصر والخطوط النظيفة.</p>',
        slug: 'villa-najd',
        status: 'published',
        published_at: '12 يناير 2026',
        categoryIds: ['1', '2'],
        image: null,
    },
    {
        id: 2,
        uuid: 'pf-cafe-rawda',
        title: 'مقهى الروضة',
        subtitle: 'هوية بصرية وتصميم مساحة ضيافة',
        body: '<p>تصميم مساحة المقهى والهوية البصرية للعلامة.</p>',
        slug: 'cafe-rawda',
        status: 'published',
        published_at: '3 يناير 2026',
        categoryIds: ['3'],
        image: null,
    },
    {
        id: 3,
        uuid: 'pf-office-tower',
        title: 'برج المكاتب — مسودة',
        subtitle: '',
        body: '',
        slug: 'office-tower',
        status: 'draft',
        published_at: null,
        categoryIds: ['1'],
        image: null,
    },
]);

/** Flat tree with depth — mirrors Taxonomy::flatTree('portfolio_category'). */
export const categories = reactive([
    { id: 1, name: 'تصميم داخلي', slug: 'interior-design', description: 'مشاريع التصميم الداخلي للمساحات السكنية والتجارية.', parent_id: null, depth: 0, sort_order: 0 },
    { id: 2, name: 'فلل سكنية', slug: 'villas', description: '', parent_id: 1, depth: 1, sort_order: 0 },
    { id: 3, name: 'هوية بصرية', slug: 'branding', description: 'شعارات وهويات العلامات التجارية.', parent_id: null, depth: 0, sort_order: 1 },
    { id: 4, name: 'مساحات تجارية', slug: 'commercial', description: 'محلات ومقاهي ومكاتب.', parent_id: null, depth: 0, sort_order: 2 },
]);

export const settings = reactive({
    sectionTitle: 'معرض الأعمال',
    sectionDescription: 'عرض وإدارة مشاريعك وأعمالك السابقة',
});

let nextProjectId = 4;
let nextCategoryId = 5;

export function findProject(idOrUuid) {
    const key = String(idOrUuid);

    return projects.find((item) => String(item.id) === key || item.uuid === key) ?? null;
}

export function findCategory(id) {
    return categories.find((item) => item.id === Number(id)) ?? null;
}

export function categoryOptions(excludeIds = []) {
    const excluded = new Set(excludeIds.map(Number));

    return categories
        .filter((item) => !excluded.has(item.id))
        .map((item) => ({
            id: String(item.id),
            label: `${'— '.repeat(item.depth)}${item.name}`,
            selectable: !categories.some((child) => child.parent_id === item.id),
        }));
}

export function parentCategoryOptions(excludeIds = []) {
    const excluded = new Set(excludeIds.map(Number));
    const options = [{ id: '', label: 'بدون تصنيف أب' }];

    for (const item of categories) {
        if (excluded.has(item.id)) {
            continue;
        }

        options.push({
            id: String(item.id),
            label: `${'— '.repeat(item.depth)}${item.name}`,
        });
    }

    return options;
}

export function descendantIds(categoryId) {
    const ids = [Number(categoryId)];
    let changed = true;

    while (changed) {
        changed = false;
        for (const item of categories) {
            if (item.parent_id !== null && ids.includes(item.parent_id) && !ids.includes(item.id)) {
                ids.push(item.id);
                changed = true;
            }
        }
    }

    return ids;
}

export function addProject({ title }) {
    const id = nextProjectId++;
    const uuid = `pf-${id}`;
    const slug = slugify(title) || `project-${id}`;

    const project = {
        id,
        uuid,
        title,
        subtitle: '',
        body: '',
        slug,
        status: 'draft',
        published_at: null,
        categoryIds: [],
        image: null,
    };

    projects.unshift(project);

    return project;
}

export function updateProject(idOrUuid, patch) {
    const project = findProject(idOrUuid);

    if (!project) {
        return null;
    }

    Object.assign(project, patch);

    return project;
}

export function deleteProjects(ids) {
    const set = new Set(ids.map(String));

    for (let i = projects.length - 1; i >= 0; i -= 1) {
        if (set.has(String(projects[i].id))) {
            projects.splice(i, 1);
        }
    }
}

export function addCategory({ name, parent_id = null }) {
    const id = nextCategoryId++;
    const parent = parent_id ? findCategory(parent_id) : null;
    const depth = parent ? parent.depth + 1 : 0;
    const siblings = categories.filter((item) => item.parent_id === (parent_id ? Number(parent_id) : null));

    const category = {
        id,
        name,
        slug: slugify(name) || `category-${id}`,
        description: '',
        parent_id: parent_id ? Number(parent_id) : null,
        depth,
        sort_order: siblings.length,
    };

    categories.push(category);
    rebuildCategoryDepths();

    return category;
}

export function updateCategory(id, patch) {
    const category = findCategory(id);

    if (!category) {
        return null;
    }

    Object.assign(category, patch);
    rebuildCategoryDepths();

    return category;
}

export function deleteCategory(id) {
    const removeIds = new Set(descendantIds(id));

    for (let i = categories.length - 1; i >= 0; i -= 1) {
        if (removeIds.has(categories[i].id)) {
            categories.splice(i, 1);
        }
    }

    for (const project of projects) {
        project.categoryIds = project.categoryIds.filter((cid) => !removeIds.has(Number(cid)));
    }
}

function rebuildCategoryDepths() {
    const byId = Object.fromEntries(categories.map((item) => [item.id, item]));

    for (const item of categories) {
        let depth = 0;
        let parentId = item.parent_id;

        while (parentId && byId[parentId]) {
            depth += 1;
            parentId = byId[parentId].parent_id;
        }

        item.depth = depth;
    }

    categories.sort((a, b) => {
        if (a.parent_id === b.parent_id) {
            return a.sort_order - b.sort_order;
        }

        return a.id - b.id;
    });
}

function slugify(value) {
    return String(value)
        .trim()
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w\u0600-\u06FF-]+/g, '')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}
