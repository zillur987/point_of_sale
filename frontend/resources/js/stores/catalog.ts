import { defineStore } from 'pinia'
import { ref } from 'vue'
import { brandApi, categoryApi, unitApi, type Brand, type Category, type Unit } from '@/api/catalog'

/** Small, rarely-changing lookup data used by product forms and POS filters. */
export const useCatalogLookups = defineStore('catalog-lookups', () => {
  const categoryTree = ref<Category[]>([])
  const brands = ref<Brand[]>([])
  const units = ref<Unit[]>([])
  const loaded = ref(false)

  async function load(force = false) {
    if (loaded.value && !force) return

    const [tree, brandRes, unitRes] = await Promise.all([
      categoryApi.tree(),
      brandApi.list({ per_page: 100, is_active: 1 }),
      unitApi.list({ per_page: 100 }),
    ])

    categoryTree.value = tree.data.data
    brands.value = brandRes.data.data
    units.value = unitRes.data.data
    loaded.value = true
  }

  return { categoryTree, brands, units, loaded, load }
})
