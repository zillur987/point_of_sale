import { defineStore } from 'pinia'
import { ref } from 'vue'
import { warehouseApi, type Warehouse } from '@/api/inventory'

/** Active warehouses for dropdowns, loaded once and shared by every inventory screen. */
export const useInventoryLookups = defineStore('inventory-lookups', () => {
  const warehouses = ref<Warehouse[]>([])
  const loaded = ref(false)

  async function load(force = false): Promise<void> {
    if (loaded.value && !force) return
    const { data } = await warehouseApi.options()
    warehouses.value = data.data
    loaded.value = true
  }

  const defaultWarehouse = (): Warehouse | undefined => warehouses.value.find((w) => w.is_default)

  return { warehouses, loaded, load, defaultWarehouse }
})
