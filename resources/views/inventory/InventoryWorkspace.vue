
<script setup>
import { computed, ref } from 'vue'

const aiMode = ref(false)
const showCreateModal = ref(false)
const showInventoryModal = ref(false)
const showDemoModal = ref(false)
const showFeatureModal = ref(false)
const csvInput = ref(null)
const search = ref('')
const statusFilter = ref('All')
const selectedType = ref('Apartment')
const selectedTab = ref('single')

const properties = ref([
  {
    id: 1,
    name: 'Skyline Residence',
    code: 'AUR-001',
    type: 'Apartment',
    location: 'Gulshan, Dhaka',
    block: 'Block A',
    area: 1850,
    price: 12500000,
    status: 'Available',
  },
  {
    id: 2,
    name: 'Green Valley Heights',
    code: 'AUR-002',
    type: 'Apartment',
    location: 'Bashundhara, Dhaka',
    block: 'Block B',
    area: 1600,
    price: 9500000,
    status: 'Reserved',
  },
  {
    id: 3,
    name: 'Prime Commercial Tower',
    code: 'AUR-003',
    type: 'Commercial',
    location: 'Motijheel, Dhaka',
    block: 'Tower 1',
    area: 2400,
    price: 35000000,
    status: 'Available',
  },
  {
    id: 4,
    name: 'Lakeview Garden',
    code: 'AUR-004',
    type: 'Plot',
    location: 'Purbachal, Dhaka',
    block: 'Sector 12',
    area: 3600,
    price: 7500000,
    status: 'Sold',
  },
])

const form = ref({
  name: '',
  type: 'Apartment',
  location: '',
  block: '',
  area: '',
  price: '',
  status: 'Available',
})

const counts = computed(() => ({
  available: properties.value.filter(
    p => p.status === 'Available',
  ).length,

  reserved: properties.value.filter(
    p => p.status === 'Reserved',
  ).length,

  sold: properties.value.filter(
    p => p.status === 'Sold',
  ).length,

  total: properties.value.length,
}))

const filteredProperties = computed(() => {
  return properties.value.filter(property => {
    const keyword = search.value.toLowerCase()

    const matchesSearch = [
      property.name,
      property.code,
      property.location,
    ].some(value => value.toLowerCase().includes(keyword))

    const matchesStatus =
      statusFilter.value === 'All' ||
      property.status === statusFilter.value

    return matchesSearch && matchesStatus
  })
})

function formatPrice(price) {
  return new Intl.NumberFormat('en-BD', {
    style: 'currency',
    currency: 'BDT',
    maximumFractionDigits: 0,
  }).format(Number(price))
}

function openCreateModal(type = 'single') {
  selectedTab.value = type

  if (type === 'single') {
    showCreateModal.value = true
  } else {
    csvInput.value?.click()
  }
}

function createProperty() {
  if (
    !form.value.name.trim() ||
    !form.value.location.trim() ||
    !form.value.price
  ) {
    alert('Please enter the property name, location, and price.')
    return
  }

  const property = {
    id: Date.now(),
    code: `AUR-${String(properties.value.length + 1).padStart(3, '0')}`,
    name: form.value.name,
    type: form.value.type,
    location: form.value.location,
    block: form.value.block || 'N/A',
    area: Number(form.value.area) || 0,
    price: Number(form.value.price),
    status: form.value.status,
  }

  properties.value.unshift(property)

  form.value = {
    name: '',
    type: 'Apartment',
    location: '',
    block: '',
    area: '',
    price: '',
    status: 'Available',
  }

  showCreateModal.value = false
}

function handleCsvUpload(event) {
  const file = event.target.files?.[0]

  if (!file) return

  if (!file.name.toLowerCase().endsWith('.csv')) {
    alert('Please select a CSV file.')
    event.target.value = ''
    return
  }

  const reader = new FileReader()

  reader.onload = () => {
    const rows = String(reader.result)
      .split(/\r?\n/)
      .filter(row => row.trim())

    if (rows.length < 2) {
      alert('The CSV file does not contain property records.')
      return
    }

    const headers = rows[0]
      .split(',')
      .map(header => header.trim().toLowerCase())

    const imported = rows.slice(1).map((row, index) => {
      const values = row.split(',')

      const record = {}

      headers.forEach((header, i) => {
        record[header] = values[i]?.trim() || ''
      })

      return {
        id: Date.now() + index,
        code: `AUR-${String(properties.value.length + index + 1).padStart(3, '0')}`,
        name: record.name || record.title || 'Imported Property',
        type: record.type || 'Apartment',
        location: record.location || '',
        block: record.block || '',
        area: Number(record.area) || 0,
        price: Number(record.price) || 0,
        status: record.status || 'Available',
      }
    })

    properties.value.unshift(...imported)

    alert(`${imported.length} properties imported.`)
  }

  reader.readAsText(file)

  event.target.value = ''
}

function removeProperty(id) {
  if (confirm('Are you sure you want to delete this property?')) {
    properties.value = properties.value.filter(
      property => property.id !== id,
    )
  }
}

function closeModals() {
  showCreateModal.value = false
  showInventoryModal.value = false
  showDemoModal.value = false
  showFeatureModal.value = false
}
</script>

<template>
  <main class="aura-workspace">

    <!-- Background decoration -->
    <div class="background-glow glow-one"></div>
    <div class="background-glow glow-two"></div>

    <div class="workspace-content">

      <!-- Brand Header -->
      <header class="aura-header">

        <div class="brand-area">

          <div class="brand-logo">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <rect x="3" y="9" width="5" height="12" rx="1" />
              <rect x="10" y="3" width="5" height="18" rx="1" />
              <rect x="17" y="7" width="4" height="14" rx="1" />
              <path d="M5 12h1M5 16h1M12 7h1M12 11h1M12 15h1M18 11h1M18 15h1" />
            </svg>
          </div>

          <div class="brand-information">

            <div class="brand-title">
              <h1>AURA</h1>

              <span class="enterprise-badge">
                Enterprise Engine
              </span>
            </div>

            <p>Next-Gen Real Estate Portfolio Hub</p>

          </div>

        </div>

        <!-- AI Mode -->
        <button
          class="ai-mode-control"
          :class="{ enabled: aiMode }"
          :aria-pressed="aiMode"
          @click="aiMode = !aiMode"
        >
          <span class="ai-sparkle">✣</span>

          <span>AI Assistant Mode</span>

          <span class="toggle-switch">
            <span class="toggle-knob"></span>
          </span>
        </button>

      </header>

      <!-- Main Title -->
      <section class="module-heading">

        <div>
          <h2>Core Inventory Modules</h2>

          <p>
            Select a workspace module to manage properties or register new listings
          </p>
        </div>

        <div class="portfolio-counter">
          Active Portfolio:
          <strong>{{ counts.total }} Units</strong>
        </div>

      </section>

      <!-- Inventory Modules -->
      <section class="module-grid">

        <!-- Inventory List -->
        <article class="module-card inventory-module">

          <div class="card-top-line"></div>

          <div class="module-card-content">

            <div class="module-card-header">

              <div class="module-icon inventory-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <rect x="3" y="3" width="8" height="8" rx="1" />
                  <rect x="13" y="3" width="8" height="5" rx="1" />
                  <rect x="13" y="10" width="8" height="11" rx="1" />
                  <rect x="3" y="13" width="8" height="8" rx="1" />
                </svg>
              </div>

              <span class="module-count inventory-count">
                {{ counts.total }} Listings Active
              </span>

            </div>

            <h3>Inventory List</h3>

            <p class="module-description">
              Browse, filter, and modify live property stock.
              Check availability status, reserved units, pricing analytics,
              and floor layout assignments.
            </p>

            <div class="module-divider"></div>

            <!-- Property statistics -->
            <div class="inventory-stat-grid">

              <div class="inventory-stat">
                <span>AVAILABLE</span>

                <strong class="available-color">
                  {{ counts.available }} Units
                </strong>
              </div>

              <div class="inventory-stat">
                <span>RESERVED</span>

                <strong class="reserved-color">
                  {{ counts.reserved }} Units
                </strong>
              </div>

              <div class="inventory-stat">
                <span>SOLD</span>

                <strong class="sold-color">
                  {{ counts.sold }} Units
                </strong>
              </div>

            </div>

            <div class="module-card-footer">

              <span class="footer-hint">
                Click to open full directory →
              </span>

              <button
                class="module-action inventory-action"
                @click="showInventoryModal = true"
              >
                View Inventory

                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path d="M4 6h16M4 12h16M4 18h16" />
                  <circle cx="2" cy="6" r="1" />
                  <circle cx="2" cy="12" r="1" />
                  <circle cx="2" cy="18" r="1" />
                </svg>
              </button>

            </div>

          </div>

        </article>

        <!-- Add Inventory -->
        <article class="module-card create-module">

          <div class="card-top-line"></div>

          <div class="module-card-content">

            <div class="module-card-header">

              <div class="module-icon create-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <rect x="3" y="3" width="18" height="18" rx="3" />
                  <path d="M12 7v10M7 12h10" />
                </svg>
              </div>

              <span class="module-count create-count">
                Fast Registration
              </span>

            </div>

            <h3>Add New Inventory</h3>

            <p class="module-description">
              Register new residential, commercial, or plot units into
              the system. Configure pricing, square footage, assigned
              block, and upload media.
            </p>

            <div class="module-divider"></div>

            <!-- Registration types -->
            <div class="registration-options">

              <button
                class="registration-option"
                @click="openCreateModal('single')"
              >
                <span class="registration-label">
                  <span class="registration-icon">▦</span>
                  Single Unit Listing
                </span>

                <span class="registration-hint">
                  Quick Form
                </span>
              </button>

              <button
                class="registration-option"
                @click="openCreateModal('bulk')"
              >
                <span class="registration-label">
                  <span class="registration-icon">▧</span>
                  Bulk CSV Upload
                </span>

                <span class="registration-hint">
                  Multi-Unit
                </span>
              </button>

            </div>

            <input
              ref="csvInput"
              type="file"
              accept=".csv"
              hidden
              @change="handleCsvUpload"
            />

            <div class="module-card-footer">

              <span class="footer-hint">
                Launch listing wizard →
              </span>

              <button
                class="module-action create-action"
                @click="openCreateModal('single')"
              >
                Create Listing

                <span class="plus-icon">＋</span>
              </button>

            </div>

          </div>

        </article>

      </section>

      <!-- Company Advertisement -->
      <section class="enterprise-ad">

        <div class="ad-main-content">

          <div class="update-badge">
            <span>🚀</span>
            ERP Platform Update v4.8
          </div>

          <h2>
            Scale Property Sales Faster with
            <span class="gradient-text">
              PropAI Cloud ERP
            </span>
          </h2>

          <p>
            Automate inventory allocations, sync live floorplans
            with sales teams worldwide, and boost lead conversions
            with built-in predictive pricing algorithms.
          </p>

          <div class="ad-actions">

            <button
              class="demo-button"
              @click="showDemoModal = true"
            >
              Request Enterprise Demo

              <span>→</span>
            </button>

            <button
              class="spotlight-button"
              @click="showFeatureModal = true"
            >
              <span>▶</span>
              Watch Feature Spotlight
            </button>

          </div>

        </div>

        <!-- Advertisement metrics -->
        <div class="ad-metrics-panel">

          <div class="metrics-grid">

            <div class="metric-box">
              <span>Avg Closing Time</span>

              <strong class="metric-green">
                -35% Faster
              </strong>

              <small>Automated workflow</small>
            </div>

            <div class="metric-box">
              <span>AI Price Accuracy</span>

              <strong class="metric-purple">
                98.4%
              </strong>

              <small>Real-time market sync</small>
            </div>

          </div>

          <div class="compliance-row">

            <span>
              <span class="shield-icon">⬡</span>
              100% ISO Compliant
            </span>

            <strong>VERIFIED ERP</strong>

          </div>

        </div>

      </section>

      <!-- AI mode panel -->
      <section
        v-if="aiMode"
        class="ai-assistant-panel"
      >

        <div class="ai-panel-icon">✣</div>

        <div>
          <h3>AI Assistant Mode Enabled</h3>

          <p>
            Your AI property assistant is ready.
            Connect your Laravel AI service to enable inventory insights,
            pricing recommendations, and natural-language search.
          </p>
        </div>

        <button @click="aiMode = false">
          Close
        </button>

      </section>

    </div>

    <!-- Create Inventory Modal -->
    <div
      v-if="showCreateModal"
      class="modal-backdrop"
      @click.self="closeModals"
    >

      <section class="create-modal">

        <header class="modal-header">

          <div>
            <span class="modal-eyebrow">
              PROPERTY REGISTRATION
            </span>

            <h2>Create New Listing</h2>

            <p>
              Register a property in your portfolio.
            </p>
          </div>

          <button
            class="modal-close"
            @click="closeModals"
          >
            ×
          </button>

        </header>

        <form @submit.prevent="createProperty">

          <div class="modal-body">

            <div class="form-grid">

              <div class="form-group full-width">
                <label>Property Name *</label>

                <input
                  v-model="form.name"
                  required
                  placeholder="e.g. Skyline Residence"
                />
              </div>

              <div class="form-group">
                <label>Property Type *</label>

                <select v-model="form.type">
                  <option>Apartment</option>
                  <option>Commercial</option>
                  <option>Plot</option>
                </select>
              </div>

              <div class="form-group">
                <label>Availability Status</label>

                <select v-model="form.status">
                  <option>Available</option>
                  <option>Reserved</option>
                  <option>Sold</option>
                </select>
              </div>

              <div class="form-group full-width">
                <label>Location *</label>

                <input
                  v-model="form.location"
                  required
                  placeholder="e.g. Gulshan, Dhaka"
                />
              </div>

              <div class="form-group">
                <label>Assigned Block</label>

                <input
                  v-model="form.block"
                  placeholder="e.g. Block A"
                />
              </div>

              <div class="form-group">
                <label>Square Footage</label>

                <input
                  v-model="form.area"
                  type="number"
                  min="0"
                  placeholder="e.g. 1850"
                />
              </div>

              <div class="form-group full-width">
                <label>Property Price (BDT) *</label>

                <input
                  v-model="form.price"
                  type="number"
                  min="1"
                  required
                  placeholder="Enter property price"
                />
              </div>

            </div>

          </div>

          <footer class="modal-footer">

            <button
              type="button"
              class="cancel-button"
              @click="closeModals"
            >
              Cancel
            </button>

            <button
              type="submit"
              class="save-button"
            >
              Save Listing
              <span>＋</span>
            </button>

          </footer>

        </form>

      </section>

    </div>

    <!-- Inventory Directory Modal -->
    <div
      v-if="showInventoryModal"
      class="modal-backdrop"
      @click.self="closeModals"
    >

      <section class="directory-modal">

        <header class="modal-header">

          <div>
            <span class="modal-eyebrow">
              INVENTORY DIRECTORY
            </span>

            <h2>Property Listings</h2>

            <p>
              {{ filteredProperties.length }} records available
            </p>
          </div>

          <button
            class="modal-close"
            @click="closeModals"
          >
            ×
          </button>

        </header>

        <div class="directory-filters">

          <input
            v-model="search"
            placeholder="Search properties..."
          />

          <select v-model="statusFilter">
            <option>All</option>
            <option>Available</option>
            <option>Reserved</option>
            <option>Sold</option>
          </select>

        </div>

        <div class="directory-table-wrapper">

          <table class="directory-table">

            <thead>
              <tr>
                <th>Property</th>
                <th>Location</th>
                <th>Area</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>

              <tr
                v-for="property in filteredProperties"
                :key="property.id"
              >

                <td>
                  <strong>{{ property.name }}</strong>

                  <small>{{ property.code }}</small>
                </td>

                <td>{{ property.location }}</td>

                <td>{{ property.area }} sqft</td>

                <td>{{ formatPrice(property.price) }}</td>

                <td>
                  <span
                    class="directory-status"
                    :class="property.status.toLowerCase()"
                  >
                    {{ property.status }}
                  </span>
                </td>

                <td>
                  <button
                    class="delete-button"
                    @click="removeProperty(property.id)"
                  >
                    Delete
                  </button>
                </td>

              </tr>

              <tr v-if="filteredProperties.length === 0">
                <td colspan="6" class="no-records">
                  No properties found.
                </td>
              </tr>

            </tbody>

          </table>

        </div>

      </section>

    </div>

    <!-- Enterprise Demo Modal -->
    <div
      v-if="showDemoModal"
      class="modal-backdrop"
      @click.self="closeModals"
    >

      <section class="simple-modal">

        <header class="modal-header">

          <div>
            <span class="modal-eyebrow">
              ENTERPRISE SOLUTIONS
            </span>

            <h2>Request Enterprise Demo</h2>

            <p>
              Explore how AURA can streamline your property operations.
            </p>
          </div>

          <button
            class="modal-close"
            @click="closeModals"
          >
            ×
          </button>

        </header>

        <div class="simple-modal-body">

          <div class="demo-feature">
            <span>✦</span>

            <div>
              <strong>Inventory Management</strong>

              <p>
                Manage property stock, unit allocation, and availability.
              </p>
            </div>
          </div>

          <div class="demo-feature">
            <span>⌂</span>

            <div>
              <strong>Sales Operations</strong>

              <p>
                Coordinate sales teams and property reservations.
              </p>
            </div>
          </div>

          <button
            class="save-button full-width-button"
            @click="closeModals"
          >
            Continue
            <span>→</span>
          </button>

        </div>

      </section>

    </div>

    <!-- Feature Spotlight Modal -->
    <div
      v-if="showFeatureModal"
      class="modal-backdrop"
      @click.self="closeModals"
    >

      <section class="simple-modal">

        <header class="modal-header">

          <div>
            <span class="modal-eyebrow">
              FEATURE SPOTLIGHT
            </span>

            <h2>Explore AURA Enterprise</h2>

            <p>
              A closer look at your property management workspace.
            </p>
          </div>

          <button
            class="modal-close"
            @click="closeModals"
          >
            ×
          </button>

        </header>

        <div class="simple-modal-body">

          <div class="feature-video-placeholder">
            <span>▶</span>

            <p>
              Feature presentation
            </p>
          </div>

          <p class="feature-note">
            Connect your product video or enterprise feature presentation here.
          </p>

        </div>

      </section>

    </div>

  </main>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.aura-workspace {
  --aura-bg: #070b1b;
  --aura-card: #0f1729;
  --aura-border: #1d2a40;
  --aura-text: #f6f8ff;
  --aura-muted: #91a0b8;
  --aura-blue: #2865f5;
  --aura-green: #08a879;

  position: relative;
  min-height: 100vh;
  overflow: hidden;
  padding: 36px;
  background: var(--aura-bg);
  color: var(--aura-text);
  font-family: Inter, "Segoe UI", sans-serif;
  isolation: isolate;
}

.aura-workspace * {
  box-sizing: border-box;
}

.workspace-content {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1600px;
  margin: 0 auto;
}

.background-glow {
  position: absolute;
  z-index: -1;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  filter: blur(150px);
  opacity: 0.12;
  pointer-events: none;
}

.glow-one {
  top: -200px;
  left: 20%;
  background: #315bff;
}

.glow-two {
  bottom: -250px;
  right: 5%;
  background: #7c3aed;
}

/* Header */

.aura-header {
  min-height: 89px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 20px;
  background: rgba(16, 24, 43, 0.9);
  border: 1px solid var(--aura-border);
  border-radius: 19px;
}

.brand-area {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-logo {
  width: 46px;
  height: 46px;
  display: grid;
  place-items: center;
  border-radius: 13px;
  background: linear-gradient(140deg, #7c3aed, #315cf5);
  color: #fff;
  box-shadow: 0 8px 25px #5545e530;
}

.brand-logo svg {
  width: 26px;
  height: 26px;
}

.brand-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-title h1 {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
  letter-spacing: -0.8px;
}

.enterprise-badge {
  padding: 4px 10px;
  border-radius: 20px;
  border: 1px solid #214b87;
  background: #102746;
  color: #63adff;
  font-size: 11px;
  font-weight: 600;
}

.brand-information p {
  margin: 5px 0 0;
  color: var(--aura-muted);
  font-size: 12px;
}

.ai-mode-control {
  min-width: 245px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 12px;
  border: 1px solid #1c2940;
  border-radius: 13px;
  background: #070d1c;
  color: #b8c4d7;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
}

.ai-sparkle {
  color: #c084fc;
  font-size: 19px;
}

.toggle-switch {
  width: 44px;
  height: 25px;
  display: flex;
  align-items: center;
  padding: 3px;
  background: #202c41;
  border-radius: 20px;
  transition: 0.2s;
}

.toggle-knob {
  width: 19px;
  height: 19px;
  background: white;
  border-radius: 50%;
  transition: 0.2s;
}

.ai-mode-control.enabled .toggle-switch {
  background: #6549e9;
}

.ai-mode-control.enabled .toggle-knob {
  transform: translateX(19px);
}

/* Heading */

.module-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin: 32px 0 20px;
}

.module-heading h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.module-heading p {
  margin: 7px 0 0;
  color: #91a0b8;
  font-size: 12px;
}

.portfolio-counter {
  padding: 9px 14px;
  border: 1px solid var(--aura-border);
  border-radius: 20px;
  color: #8fa8cb;
  font-size: 12px;
  white-space: nowrap;
}

.portfolio-counter strong {
  color: #f1f5ff;
}

/* Module cards */

.module-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 28px;
}

.module-card {
  position: relative;
  overflow: hidden;
  min-height: 478px;
  border: 1px solid #202b41;
  border-radius: 27px;
  background: rgba(15, 23, 41, 0.93);
}

.card-top-line {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.inventory-module .card-top-line {
  background: linear-gradient(90deg, #347cf9, #a44bf2);
}

.create-module .card-top-line {
  background: linear-gradient(90deg, #06a878, #05b9c3);
}

.module-card-content {
  display: flex;
  flex-direction: column;
  min-height: 478px;
  padding: 36px;
}

.module-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
}

.module-icon {
  width: 65px;
  height: 65px;
  display: grid;
  place-items: center;
  border-radius: 18px;
}

.module-icon svg {
  width: 27px;
  height: 27px;
}

.inventory-icon {
  color: #66a9ff;
  background: #12264a;
  border: 1px solid #1d467e;
}

.create-icon {
  color: #35d8ac;
  background: #0b302f;
  border: 1px solid #10584c;
}

.module-count {
  padding: 7px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.inventory-count {
  color: #62aaff;
  background: #102442;
  border: 1px solid #1c3b68;
}

.create-count {
  color: #2cd5a6;
  background: #0b2a2c;
  border: 1px solid #104d45;
}

.module-card h3 {
  margin: 28px 0 12px;
  color: #f7f9ff;
  font-size: 27px;
  font-weight: 700;
  letter-spacing: -0.8px;
}

.module-description {
  min-height: 58px;
  margin: 0;
  color: #91a0b8;
  font-size: 14px;
  line-height: 1.8;
}

.module-divider {
  height: 1px;
  margin: 26px 0 18px;
  background: #1c293e;
}

/* Inventory statistics */

.inventory-stat-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 13px;
}

.inventory-stat {
  min-height: 89px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 8px;
  padding: 13px;
  border: 1px solid #1e2b40;
  border-radius: 15px;
  background: #10192b;
}

.inventory-stat span {
  color: #91a0b8;
  font-size: 10px;
  font-weight: 700;
}

.inventory-stat strong {
  font-size: 19px;
  font-weight: 700;
}

.available-color {
  color: #24d7a3;
}

.reserved-color {
  color: #ffbe2e;
}

.sold-color {
  color: #61a6ff;
}

/* Card footer */

.module-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
  margin-top: auto;
  padding-top: 30px;
}

.footer-hint {
  color: #94a4bd;
  font-size: 12px;
}

.module-action {
  min-height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 11px 22px;
  border: 0;
  border-radius: 14px;
  color: white;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s;
}

.module-action:hover {
  transform: translateY(-2px);
}

.inventory-action {
  background: linear-gradient(100deg, #2872f5, #2563eb);
  box-shadow: 0 8px 20px #2865f520;
}

.inventory-action svg {
  width: 16px;
  height: 16px;
}

.create-action {
  background: linear-gradient(100deg, #079c77, #05a47d);
  box-shadow: 0 8px 20px #05a47d20;
}

.plus-icon {
  font-size: 19px;
}

/* Registration options */

.registration-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.registration-option {
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 13px;
  border: 1px solid #1d2a40;
  border-radius: 13px;
  background: #10192b;
  color: #c0cbda;
  font-size: 12px;
  cursor: pointer;
  transition: 0.2s;
}

.registration-option:hover {
  border-color: #276b70;
  background: #11232e;
}

.registration-label {
  display: flex;
  align-items: center;
  gap: 10px;
}

.registration-icon {
  color: #30d7ad;
  font-size: 16px;
}

.registration-hint {
  color: #697b96;
  font-size: 11px;
}

/* Advertisement */

.enterprise-ad {
  position: relative;
  display: grid;
  grid-template-columns: minmax(0, 1.55fr) minmax(320px, 0.8fr);
  gap: 30px;
  align-items: center;
  min-height: 305px;
  margin-top: 36px;
  padding: 38px;
  border: 1px solid #263c7a;
  border-radius: 28px;
  background:
    radial-gradient(
      ellipse at 50% 100%,
      #332060 0%,
      transparent 65%
    ),
    linear-gradient(115deg, #172b65, #111b39 65%, #13203e);
}

.ad-main-content {
  min-width: 0;
}

.update-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 13px;
  border: 1px solid #28488a;
  border-radius: 20px;
  background: #172e64;
  color: #65adff;
  font-size: 11px;
  font-weight: 600;
}

.ad-main-content h2 {
  margin: 22px 0 14px;
  color: #fff;
  font-size: clamp(24px, 2.5vw, 35px);
  font-weight: 800;
  line-height: 1.3;
  letter-spacing: -1px;
}

.gradient-text {
  background: linear-gradient(90deg, #69a8ff, #bd77f5, #ff72c6);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.ad-main-content > p {
  max-width: 750px;
  margin: 0;
  color: #c1cce5;
  font-size: 14px;
  line-height: 1.8;
}

.ad-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 26px;
}

.demo-button,
.spotlight-button {
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 12px 19px;
  border-radius: 13px;
  color: white;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s;
}

.demo-button {
  border: 1px solid #3a63fa;
  background: linear-gradient(100deg, #286bf5, #5440e9);
  box-shadow: 0 8px 20px #3552f030;
}

.spotlight-button {
  border: 1px solid #354263;
  background: #202b49;
}

.demo-button:hover,
.spotlight-button:hover {
  transform: translateY(-2px);
}

/* Metrics */

.ad-metrics-panel {
  padding: 18px;
  border: 1px solid #1b2a49;
  border-radius: 20px;
  background: #080f22;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 15px;
}

.metric-box {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px 14px;
  border: 1px solid #1d2a40;
  border-radius: 15px;
  background: #0d172a;
}

.metric-box > span {
  color: #a0aec3;
  font-size: 11px;
}

.metric-box strong {
  font-size: 23px;
  font-weight: 800;
}

.metric-green {
  color: #25dba7;
}

.metric-purple {
  color: #cb83ff;
}

.metric-box small {
  color: #687c99;
  font-size: 10px;
}

.compliance-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 16px;
  padding: 13px;
  border: 1px solid #1e3158;
  border-radius: 12px;
  background: #101b38;
}

.compliance-row > span {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #8cc6ff;
  font-size: 11px;
}

.compliance-row strong {
  color: #64aaff;
  font-size: 10px;
}

.shield-icon {
  font-size: 18px;
}

/* AI panel */

.ai-assistant-panel {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 25px;
  padding: 24px;
  border: 1px solid #4c3b92;
  border-radius: 18px;
  background: linear-gradient(100deg, #17183b, #10192e);
}

.ai-panel-icon {
  width: 45px;
  height: 45px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 13px;
  background: #30215c;
  color: #c7a3ff;
  font-size: 25px;
}

.ai-assistant-panel h3 {
  margin: 0 0 7px;
  font-size: 15px;
}

.ai-assistant-panel p {
  margin: 0;
  color: #93a3bd;
  font-size: 12px;
  line-height: 1.7;
}

.ai-assistant-panel button {
  margin-left: auto;
  padding: 9px 15px;
  border: 1px solid #354263;
  border-radius: 8px;
  background: #17213a;
  color: #c5d0e4;
  cursor: pointer;
}

/* Modal */

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: grid;
  place-items: center;
  padding: 20px;
  background: rgba(3, 7, 18, 0.82);
  backdrop-filter: blur(8px);
}

.create-modal,
.directory-modal,
.simple-modal {
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  border: 1px solid #26364f;
  border-radius: 20px;
  background: #0e1729;
  box-shadow: 0 30px 100px #00000070;
}

.create-modal {
  max-width: 650px;
}

.directory-modal {
  max-width: 1100px;
}

.simple-modal {
  max-width: 550px;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding: 25px;
  border-bottom: 1px solid #1e2a40;
}

.modal-eyebrow {
  color: #5da8ff;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1px;
}

.modal-header h2 {
  margin: 10px 0 7px;
  color: #f5f7ff;
  font-size: 22px;
}

.modal-header p {
  margin: 0;
  color: #8d9db7;
  font-size: 12px;
}

.modal-close {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  border: 1px solid #26364f;
  border-radius: 9px;
  background: #111d32;
  color: #b4c0d5;
  font-size: 23px;
  cursor: pointer;
}

.modal-body {
  padding: 25px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 9px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  color: #a9b8ce;
  font-size: 12px;
  font-weight: 600;
}

.form-group input,
.form-group select {
  width: 100%;
  min-height: 43px;
  padding: 12px;
  border: 1px solid #27364d;
  border-radius: 9px;
  outline: none;
  background: #0a1222;
  color: #e7edf8;
  font-family: inherit;
  font-size: 12px;
}

.form-group input:focus,
.form-group select:focus {
  border-color: #3e76f6;
  box-shadow: 0 0 0 3px #2865f51a;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 25px;
  border-top: 1px solid #1e2a40;
}

.cancel-button,
.save-button {
  min-height: 42px;
  padding: 11px 20px;
  border-radius: 9px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.cancel-button {
  border: 1px solid #2b3b54;
  background: #111d32;
  color: #aab9d0;
}

.save-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 1px solid #087c60;
  background: linear-gradient(100deg, #079c77, #05a47d);
  color: #fff;
}

/* Directory */

.directory-filters {
  display: flex;
  gap: 12px;
  padding: 20px 25px;
}

.directory-filters input,
.directory-filters select {
  min-height: 42px;
  padding: 10px 13px;
  border: 1px solid #27364d;
  border-radius: 9px;
  outline: none;
  background: #0a1222;
  color: #dbe4f4;
  font-size: 12px;
}

.directory-filters input {
  flex: 1;
}

.directory-table-wrapper {
  overflow-x: auto;
}

.directory-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 750px;
}

.directory-table th {
  padding: 14px;
  background: #111d32;
  color: #8598b5;
  font-size: 11px;
  text-align: left;
}

.directory-table td {
  padding: 15px 14px;
  border-bottom: 1px solid #1c293e;
  color: #b7c4d8;
  font-size: 12px;
}

.directory-table td strong {
  display: block;
  color: #f2f5fc;
}

.directory-table td small {
  display: block;
  margin-top: 5px;
  color: #7285a2;
}

.directory-status {
  padding: 6px 9px;
  border-radius: 20px;
  font-size: 10px;
}

.directory-status.available {
  background: #103a31;
  color: #37d9aa;
}

.directory-status.reserved {
  background: #3b2c17;
  color: #ffbf40;
}

.directory-status.sold {
  background: #3b202b;
  color: #ff829d;
}

.delete-button {
  padding: 7px 10px;
  border: 1px solid #5b2938;
  border-radius: 7px;
  background: #301725;
  color: #ff8ba5;
  font-size: 11px;
  cursor: pointer;
}

.no-records {
  text-align: center;
  padding: 30px !important;
  color: #8798b2 !important;
}

/* Demo */

.simple-modal-body {
  padding: 25px;
}

.demo-feature {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-bottom: 20px;
  padding: 17px;
  border: 1px solid #23344d;
  border-radius: 12px;
  background: #101c31;
}

.demo-feature > span {
  color: #63a9ff;
  font-size: 22px;
}

.demo-feature strong {
  color: #eaf0fc;
  font-size: 13px;
}

.demo-feature p {
  margin: 7px 0 0;
  color: #8d9db7;
  font-size: 12px;
  line-height: 1.6;
}

.full-width-button {
  width: 100%;
  margin-top: 10px;
}

.feature-video-placeholder {
  min-height: 220px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 15px;
  border: 1px solid #263958;
  border-radius: 14px;
  background: linear-gradient(140deg, #142449, #17143d);
}

.feature-video-placeholder > span {
  width: 55px;
  height: 55px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: #3266ed;
  color: white;
  font-size: 20px;
}

.feature-video-placeholder p {
  color: #9bb0d0;
  font-size: 12px;
}

.feature-note {
  color: #8d9db7;
  font-size: 12px;
  line-height: 1.7;
}

/* Responsive */

@media (max-width: 1100px) {
  .aura-workspace {
    padding: 25px;
  }

  .module-grid {
    gap: 18px;
  }

  .module-card-content {
    padding: 25px;
  }

  .module-card h3 {
    font-size: 23px;
  }

  .enterprise-ad {
    grid-template-columns: 1fr;
  }

  .ad-metrics-panel {
    max-width: 100%;
  }
}

@media (max-width: 760px) {
  .aura-workspace {
    padding: 15px;
  }

  .aura-header {
    align-items: flex-start;
    flex-direction: column;
    padding: 17px;
  }

  .ai-mode-control {
    width: 100%;
  }

  .module-heading {
    align-items: flex-start;
    flex-direction: column;
  }

  .module-grid {
    grid-template-columns: 1fr;
  }

  .module-card,
  .module-card-content {
    min-height: auto;
  }

  .module-card-content {
    padding: 24px;
  }

  .module-card-footer {
    margin-top: 30px;
  }

  .enterprise-ad {
    padding: 25px;
    border-radius: 20px;
  }

  .ad-main-content h2 {
    font-size: 25px;
  }

  .metrics-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 480px) {
  .brand-title {
    flex-wrap: wrap;
  }

  .brand-title h1 {
    font-size: 18px;
  }

  .brand-information p {
    font-size: 10px;
  }

  .module-card h3 {
    font-size: 22px;
  }

  .inventory-stat-grid {
    gap: 7px;
  }

  .inventory-stat {
    padding: 10px;
  }

  .inventory-stat strong {
    font-size: 14px;
  }

  .module-card-footer {
    align-items: flex-start;
    flex-direction: column;
  }

  .module-action {
    width: 100%;
  }

  .ad-actions {
    flex-direction: column;
  }

  .ad-actions button {
    width: 100%;
  }

  .compliance-row {
    align-items: flex-start;
    flex-direction: column;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group.full-width {
    grid-column: auto;
  }

  .directory-filters {
    flex-direction: column;
  }

  .ai-assistant-panel {
    align-items: flex-start;
    flex-wrap: wrap;
  }
}
</style>
