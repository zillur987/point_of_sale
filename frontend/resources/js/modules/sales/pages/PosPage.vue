<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { warehouseApi } from '@/api/inventory'
import type { Warehouse } from '@/api/inventory'
import { posApi } from '@/api/sales'
import type { PosProduct, SaleRecord } from '@/api/sales'

const router=useRouter()
const warehouses=ref<Warehouse[]>([])
const warehouseId=ref<number|null>(null)
const search=ref('')
const products=ref<PosProduct[]>([])
const cart=ref<Array<{product:PosProduct;quantity:number}>>([])
const loading=ref(false), saving=ref(false)
const discount=ref(0), tax=ref(0), paid=ref(0)
const paymentMethod=ref<'cash'|'card'|'mobile_banking'|'bank'>('cash')
const paymentReference=ref('')
const note=ref('')
const error=ref('')
const completed=ref<SaleRecord|null>(null)
let searchTimer:ReturnType<typeof setTimeout>|undefined
const money=(minor:number)=>new Intl.NumberFormat('en-BD',{style:'currency',currency:'BDT',maximumFractionDigits:2}).format((minor||0)/100)
const subtotal=computed(()=>cart.value.reduce((s,row)=>s+Math.round(row.product.sale_price*row.quantity),0))
const total=computed(()=>Math.max(0,subtotal.value-Math.max(0,discount.value)+Math.max(0,tax.value)))
const change=computed(()=>Math.max(0,paid.value-total.value))
const discountDisplay=computed({get:()=>discount.value/100,set:(v:number)=>discount.value=Math.max(0,Math.round((Number(v)||0)*100))})
const taxDisplay=computed({get:()=>tax.value/100,set:(v:number)=>tax.value=Math.max(0,Math.round((Number(v)||0)*100))})
const paidDisplay=computed({get:()=>paid.value/100,set:(v:number)=>paid.value=Math.max(0,Math.round((Number(v)||0)*100))})
const due=computed(()=>Math.max(0,total.value-paid.value))
const loadCatalog=async()=>{
 if(!warehouseId.value)return
 loading.value=true; error.value=''
 try{products.value=(await posApi.catalog(warehouseId.value,search.value.trim())).data.data}
 catch(e:any){error.value=e?.response?.data?.message||'Unable to load products.'}
 finally{loading.value=false}
}
watch(search,()=>{clearTimeout(searchTimer);searchTimer=setTimeout(loadCatalog,250)})
watch(warehouseId,()=>{cart.value=[];loadCatalog()})
onMounted(async()=>{try{warehouses.value=(await warehouseApi.options()).data.data;warehouseId.value=warehouses.value.find(w=>w.is_default)?.id??warehouses.value[0]?.id??null}catch(e:any){error.value=e?.response?.data?.message||'Unable to load warehouses.'}})
onUnmounted(()=>clearTimeout(searchTimer))
function add(product:PosProduct){
 const row=cart.value.find(x=>x.product.id===product.id)
 if(row){if(row.quantity+1>Number(product.available_quantity))return error.value='Not enough stock available.';row.quantity+=1}
 else {if(Number(product.available_quantity)<=0)return error.value='This product is out of stock.';cart.value.push({product,quantity:1})}
 error.value=''
}
function setQty(id:number,qty:number){
 const row=cart.value.find(x=>x.product.id===id);if(!row)return
 if(qty<=0){cart.value=cart.value.filter(x=>x.product.id!==id);return}
 if(!row.product.allow_decimal)qty=Math.floor(qty)
 if(qty>Number(row.product.available_quantity)){error.value='Quantity exceeds available stock.';return}
 row.quantity=qty;error.value=''
}
function barcodeEnter(){if(products.value.length===1)add(products.value[0]);search.value=''}
async function checkout(){
 if(!warehouseId.value||!cart.value.length)return
 if(paid.value<total.value){error.value='Received amount is less than the total.';return}
 saving.value=true;error.value=''
 try{
  const response=await posApi.checkout({warehouse_id:warehouseId.value,request_key:crypto.randomUUID(),items:cart.value.map(r=>({product_variant_id:r.product.id,quantity:r.quantity})),discount_amount:discount.value,tax_amount:tax.value,paid_amount:paid.value,payment_method:paymentMethod.value,payment_reference:paymentReference.value||null,note:note.value||null})
  completed.value=response.data.data
 }catch(e:any){error.value=e?.response?.data?.message||Object.values(e?.response?.data?.errors||{}).flat().join(' ')||'Checkout failed. Please try again.'}
 finally{saving.value=false}
}
function escapeHtml(value: string): string {
    return value.replace(/[&<>"']/g, (char) => {
        const entities: Record<string, string> = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }

        return entities[char] || char
    })
}

function printReceipt() {
 if(!completed.value)return
 const s=completed.value
 const rows=(s.items||[]).map(i=>`<tr><td>${escapeHtml(i.product_name)} ${escapeHtml(i.variant_name||'')}<small>${escapeHtml(i.sku||'')}</small></td><td>${i.quantity}</td><td>${money(i.line_total)}</td></tr>`).join('')
 const w=window.open('','_blank','width=420,height=700');if(!w)return
 w.document.write(`<html><head><title>${s.invoice_no}</title><style>body{font:14px Arial;padding:20px}h2,p{text-align:center}table{width:100%;border-collapse:collapse}td{padding:8px 2px;border-bottom:1px dashed #aaa}td:last-child{text-align:right}small{display:block;color:#666}.total{font-weight:bold;font-size:18px}</style></head><body><h2>SALES RECEIPT</h2><p>${s.invoice_no}<br>${new Date(s.sold_at).toLocaleString()}</p><table>${rows}</table><p>Subtotal: ${money(s.subtotal)}<br>Discount: ${money(s.discount_amount)}<br>Tax: ${money(s.tax_amount)}</p><p class="total">Total: ${money(s.grand_total)}</p><p>Paid: ${money(s.paid_amount)}<br>Change: ${money(s.change_amount)}</p><p>Thank you!</p><script>window.print()<\/script></body></html>`);w.document.close()
}
function newSale(){completed.value=null;cart.value=[];discount.value=0;tax.value=0;paid.value=0;note.value='';paymentReference.value='';search.value='';loadCatalog()}
</script>

<template>
 <div class="pos-page">
  <header class="pos-header"><div><span class="eyebrow">CHECKOUT WORKSPACE</span><h1>Point of Sale</h1><p>Build a cart, collect payment and complete the sale.</p></div><button class="history-btn" @click="router.push({name:'sales.index'})">Sales history ↗</button></header>
  <div v-if="error" class="alert">{{ error }}</div>
  <section v-if="completed" class="success-panel">
   <div class="success-icon">✓</div><h2>Sale completed</h2><p>{{completed.invoice_no}}</p><strong>{{money(completed.grand_total)}}</strong>
   <div class="success-actions"><button class="primary" @click="printReceipt">Print receipt</button><button class="secondary" @click="newSale">New sale</button></div>
  </section>
  <div v-else class="pos-layout">
   <section class="catalog-panel">
    <div class="toolbar"><label>Warehouse<select v-model="warehouseId"><option v-for="w in warehouses" :key="w.id" :value="w.id">{{w.name}}</option></select></label><span class="stock-label">Stock-aware catalog</span></div>
    <div class="search-wrap"><span>⌕</span><input v-model="search" placeholder="Search product, SKU or scan barcode..." @keyup.enter="barcodeEnter"></div>
    <div v-if="loading" class="empty">Loading products...</div>
    <div v-else-if="!products.length" class="empty">No matching stock items. Try another search.</div>
    <div v-else class="product-grid">
     <button v-for="p in products" :key="p.id" class="product-card" :disabled="Number(p.available_quantity)<=0" @click="add(p)">
      <span class="product-icon">▦</span><strong>{{p.name}}</strong><small>{{p.variant_name||'Default'}} · {{p.sku}}</small><b>{{money(p.sale_price)}}</b><em>{{p.available_quantity}} available</em>
     </button>
    </div>
   </section>
   <aside class="cart-panel">
    <div class="cart-title"><div><h2>Current order</h2><small>{{cart.length}} line items</small></div><button class="clear-btn" @click="cart=[]">Clear</button></div>
    <div v-if="!cart.length" class="cart-empty"><span>＋</span><b>Your cart is empty</b><small>Select products to start a sale.</small></div>
    <div v-else class="cart-lines">
     <div v-for="row in cart" :key="row.product.id" class="cart-line">
      <div class="line-info"><b>{{row.product.name}}</b><small>{{row.product.sku}} · {{money(row.product.sale_price)}}</small><strong>{{money(row.product.sale_price*row.quantity)}}</strong></div>
      <div class="qty-control"><button @click="setQty(row.product.id,row.quantity-1)">−</button><input type="number" min="0.001" :step="row.product.allow_decimal?0.001:1" :value="row.quantity" @change="setQty(row.product.id,Number(($event.target as HTMLInputElement).value))"><button @click="setQty(row.product.id,row.quantity+1)">+</button></div>
     </div>
    </div>
    <div class="totals">
     <div><span>Subtotal</span><b>{{money(subtotal)}}</b></div>
     <label><span>Discount (৳)</span><input v-model.number="discountDisplay" type="number" min="0" step="0.01"></label>
     <label><span>Tax (৳)</span><input v-model.number="taxDisplay" type="number" min="0" step="0.01"></label>
     <div class="grand"><span>Total due</span><b>{{money(total)}}</b></div>
     <label><span>Payment method</span><select v-model="paymentMethod"><option value="cash">Cash</option><option value="card">Card</option><option value="mobile_banking">Mobile banking</option><option value="bank">Bank transfer</option></select></label>
     <label><span>Received (৳)</span><input v-model.number="paidDisplay" type="number" min="0" step="0.01"></label>
     <div><span>Change</span><b>{{money(change)}}</b></div><div v-if="due>0" class="due"><span>Remaining</span><b>{{money(due)}}</b></div>
     <input v-model="paymentReference" placeholder="Payment reference (optional)"><textarea v-model="note" placeholder="Sale note (optional)"></textarea>
     <button class="checkout" :disabled="!cart.length||saving||!warehouseId||due>0" @click="checkout">{{saving?'Processing...':`Complete sale · ${money(total)}`}}</button>
     <small class="money-hint">Amounts are sent to the server in minor currency units.</small>
    </div>
   </aside>
  </div>
 </div>
</template>

<style scoped>
.pos-page{color:#172033}.pos-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}.eyebrow{font-size:10px;letter-spacing:1.5px;color:#64748b;font-weight:800}.pos-header h1{font-size:25px;margin:5px 0}.pos-header p{color:#7b8798;margin:0;font-size:13px}.history-btn,.secondary,.clear-btn{background:#fff;border:1px solid #e2e8f0;border-radius:9px;padding:10px 14px;color:#334155;cursor:pointer}.alert{padding:12px;background:#fff1f2;color:#be123c;border-radius:8px;margin-bottom:14px}.pos-layout{display:grid;grid-template-columns:minmax(0,1fr) 390px;gap:18px;align-items:start}.catalog-panel,.cart-panel{background:#fff;border:1px solid #e7ebf1;border-radius:14px;min-width:0}.catalog-panel{padding:18px;min-height:620px}.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}.toolbar label,.totals label{display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:12px;color:#64748b;font-weight:700}.toolbar select,.totals select,.totals input,.totals textarea{border:1px solid #e2e8f0;border-radius:8px;padding:9px;background:#fff;color:#172033}.stock-label{font-size:11px;color:#15803d;background:#f0fdf4;padding:7px 9px;border-radius:20px}.search-wrap{display:flex;gap:10px;align-items:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:0 13px;margin-bottom:16px}.search-wrap span{font-size:23px;color:#94a3b8}.search-wrap input{width:100%;border:0;background:transparent;padding:13px 0;outline:0}.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:11px}.product-card{text-align:left;background:#fff;border:1px solid #e8edf3;border-radius:11px;padding:13px;display:flex;flex-direction:column;gap:7px;cursor:pointer;min-height:158px}.product-card:hover{border-color:#94a3b8;box-shadow:0 5px 18px #0f172a0d}.product-card:disabled{opacity:.45;cursor:not-allowed}.product-icon{width:30px;height:30px;border-radius:8px;background:#f1f5f9;display:grid;place-items:center;color:#475569}.product-card strong{font-size:13px}.product-card small,.product-card em{font-size:10px;color:#64748b;font-style:normal}.product-card b{font-size:13px;margin-top:auto}.cart-panel{padding:17px;position:sticky;top:15px}.cart-title{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #edf0f4;padding-bottom:14px}.cart-title h2{font-size:16px;margin:0 0 4px}.cart-title small{font-size:11px;color:#94a3b8}.clear-btn{padding:6px 9px;font-size:11px}.cart-empty,.empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:210px;color:#64748b;gap:7px}.cart-empty span{font-size:28px;background:#f1f5f9;border-radius:50%;padding:7px 14px}.cart-empty b{font-size:13px;color:#334155}.cart-empty small{font-size:11px}.cart-lines{max-height:270px;overflow:auto}.cart-line{padding:13px 0;border-bottom:1px solid #f0f2f5;display:flex;gap:8px;justify-content:space-between}.line-info{display:flex;flex-direction:column;gap:4px;min-width:0}.line-info b{font-size:12px}.line-info small{font-size:10px;color:#94a3b8}.line-info strong{font-size:12px}.qty-control{display:flex;align-items:center;gap:3px}.qty-control button{border:1px solid #e2e8f0;background:#fff;border-radius:5px;width:25px;height:26px}.qty-control input{width:42px;text-align:center;border:1px solid #e2e8f0;border-radius:5px;padding:4px}.totals{display:flex;flex-direction:column;gap:11px;padding-top:14px}.totals>div{display:flex;justify-content:space-between;font-size:12px;color:#64748b}.totals>div b{color:#172033}.totals label input,.totals label select{width:130px}.totals textarea{resize:vertical;min-height:55px}.totals .grand{padding:12px 0;border-top:1px solid #edf0f4;border-bottom:1px solid #edf0f4;font-size:14px;color:#172033}.totals .grand b{font-size:20px}.totals .due{color:#be123c}.checkout,.primary{border:0;background:#172033;color:#fff;border-radius:9px;padding:13px;font-weight:700;cursor:pointer}.checkout:disabled{opacity:.4;cursor:not-allowed}.money-hint{font-size:10px;color:#94a3b8;text-align:center}.success-panel{background:#fff;border:1px solid #e5eaf0;border-radius:15px;max-width:520px;margin:50px auto;padding:40px;text-align:center}.success-icon{width:48px;height:48px;border-radius:50%;background:#dcfce7;color:#15803d;display:grid;place-items:center;margin:auto;font-size:25px}.success-panel h2{margin:15px 0 5px}.success-panel p{color:#64748b}.success-panel>strong{font-size:28px}.success-actions{display:flex;gap:10px;justify-content:center;margin-top:24px}.primary{padding:10px 16px}.success-panel .secondary{padding:10px 16px}@media(max-width:1050px){.pos-layout{grid-template-columns:1fr}.cart-panel{position:static}}@media(max-width:600px){.pos-header{align-items:flex-start;gap:10px}.pos-header h1{font-size:21px}.history-btn{font-size:11px}.catalog-panel{padding:12px}.product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.cart-panel{padding:13px}}
</style>
