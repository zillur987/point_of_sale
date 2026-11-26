<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Supplier extends Model
{
    protected $fillable = ['name','company_name','email','phone','tax_number','address','city','country','notes','is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function purchases(): HasMany { return $this->hasMany(Purchase::class); }
}
