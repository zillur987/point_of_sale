<?php
namespace App\Models;
use App\Enums\Sales\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SalePayment extends Model {
 protected $fillable=['sale_id','method','amount','reference'];
 protected function casts(): array { return ['method'=>PaymentMethod::class,'amount'=>'integer']; }
 public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
}
