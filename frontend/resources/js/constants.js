
export const CONTACT_STATUSES = [
    { value: 'lead', label: 'Lead', color: 'info' },
    { value: 'active', label: 'Active', color: 'success' },
    { value: 'inactive', label: 'Inactive', color: 'secondary' },
];

export const LEAD_STATUSES = [
    { value: 'new', label: 'New', color: 'secondary' },
    { value: 'contacted', label: 'Contacted', color: 'info' },
    { value: 'qualified', label: 'Qualified', color: 'primary' },
    { value: 'unqualified', label: 'Unqualified', color: 'danger' },
    { value: 'converted', label: 'Converted', color: 'success' },
];

export const LEAD_SOURCES = [
    { value: 'website', label: 'Website' },
    { value: 'referral', label: 'Referral' },
    { value: 'social_media', label: 'Social media' },
    { value: 'email_campaign', label: 'Email campaign' },
    { value: 'cold_call', label: 'Cold call' },
    { value: 'event', label: 'Event' },
    { value: 'other', label: 'Other' },
];

export const DEAL_STAGES = [
    { value: 'new', label: 'New', color: 'secondary' },
    { value: 'qualified', label: 'Qualified', color: 'info' },
    { value: 'proposal', label: 'Proposal', color: 'primary' },
    { value: 'negotiation', label: 'Negotiation', color: 'warning' },
    { value: 'won', label: 'Won', color: 'success' },
    { value: 'lost', label: 'Lost', color: 'danger' },
];

/** The happy path shown as a stepper (Lost is a side exit). */
export const DEAL_FLOW = [
    'new',
    'qualified',
    'proposal',
    'negotiation',
    'won',
];

export const PROPERTY_TYPES = [
    { value: 'apartment', label: 'Apartment' },
    { value: 'villa', label: 'Villa' },
    { value: 'townhouse', label: 'Townhouse' },
    { value: 'penthouse', label: 'Penthouse' },
    { value: 'plot', label: 'Plot' },
    { value: 'commercial', label: 'Commercial' },
    { value: 'office', label: 'Office' },
];

export const PROPERTY_STATUSES = [
    { value: 'ready', label: 'Ready', color: 'success' },
    { value: 'off-plan', label: 'Off-plan', color: 'info' },
];

export const INVENTORY_STATUSES = [
    { value: 'draft', label: 'Draft', color: 'secondary' },
    { value: 'available', label: 'Available', color: 'success' },
    { value: 'reserved', label: 'Reserved', color: 'warning' },
    { value: 'sold', label: 'Sold', color: 'primary' },
    { value: 'rented', label: 'Rented', color: 'info' },
    { value: 'off-market', label: 'Off-market', color: 'dark' },
];

export const LISTING_TYPES = [
    { value: 'sale', label: 'For sale' },
    { value: 'rent', label: 'For rent' },
];

export const FURNISHING = [
    { value: 'unfurnished', label: 'Unfurnished' },
    { value: 'semi-furnished', label: 'Semi-furnished' },
    { value: 'furnished', label: 'Furnished' },
];

export const INVENTORY_AMENITIES = [
    'Pool', 'Gym', 'Parking', 'Security', 'Garden', 'Balcony', 'Sea view',
    'Concierge', 'Smart home', 'Maid room', 'Community pool', 'Beach access',
    'Private terrace', 'Generator', 'Lift', 'Metro',
];

export const AD_CHANNELS = [
    { value: 'website', label: 'Website', icon: 'bi-globe' },
    { value: 'facebook', label: 'Facebook', icon: 'bi-facebook' },
    { value: 'instagram', label: 'Instagram', icon: 'bi-instagram' },
    { value: 'google', label: 'Google Ads', icon: 'bi-google' },
    { value: 'portal', label: 'Property portal', icon: 'bi-building' },
];

export const AD_STATUSES = [
    { value: 'draft', label: 'Draft', color: 'secondary' },
    { value: 'scheduled', label: 'Scheduled', color: 'info' },
    { value: 'live', label: 'Live', color: 'success' },
    { value: 'paused', label: 'Paused', color: 'warning' },
    { value: 'ended', label: 'Ended', color: 'dark' },
];

// Company sizes
export const COMPANY_SIZES = [
    { value: '1-10', label: '1-10 employees' },
    { value: '11-50', label: '11-50 employees' },
    { value: '51-200', label: '51-200 employees' },
    { value: '201-500', label: '201-500 employees' },
    { value: '501+', label: '501+ employees' },
];

// Common currencies; confirm these values match your backend.
export const CURRENCIES = [
    { value: 'AED', label: 'AED - UAE Dirham' },
    { value: 'USD', label: 'USD - US Dollar' },
    { value: 'BDT', label: 'BDT - Bangladeshi Taka' },
    { value: 'EUR', label: 'EUR - Euro' },
    { value: 'GBP', label: 'GBP - British Pound' },
    { value: 'SAR', label: 'SAR - Saudi Riyal' },
];

const byValue = (list) =>
    Object.fromEntries(list.map((item) => [item.value, item]));

export const contactStatusMeta = byValue(CONTACT_STATUSES);
export const dealStageMeta = byValue(DEAL_STAGES);
export const leadStatusMeta = byValue(LEAD_STATUSES);
export const leadSourceMeta = byValue(LEAD_SOURCES);
export const propertyTypeMeta = byValue(PROPERTY_TYPES);
export const propertyStatusMeta = byValue(PROPERTY_STATUSES);
export const inventoryStatusMeta = byValue(INVENTORY_STATUSES);
export const listingTypeMeta = byValue(LISTING_TYPES);
export const furnishingMeta = byValue(FURNISHING);
export const adChannelMeta = byValue(AD_CHANNELS);
export const adStatusMeta = byValue(AD_STATUSES);
export const companySizeMeta = byValue(COMPANY_SIZES);