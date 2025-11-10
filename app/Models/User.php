<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel; // <== استيراد كلاس Filament\Panel
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable implements FilamentUser
{
    use  HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
     protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'gender',
        'address',
        'nationality',
        'phone_number',
        'profile_details',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->pluck('name')->contains($roleName);
    }

    /**
     * Determine if the user can access the given Filament panel.
     * <== تم تعديل توقيع الدالة هنا لاستقبال كائن Filament\Panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // اسم اللوحة الافتراضي لـ Filament Admin هو 'admin'
        // يمكنك الوصول إلى معرف اللوحة عبر $panel->getId()
        if ($panel->getId() === 'admin') {
            return $this->hasRole('Admin');
        }

        // إذا كان لديك لوحات Filament إضافية، يمكنك تعريفها هنا
        // مثال:
         if ($panel->getId() === 'manager') {
             return $this->hasRole('Manager');
         }

         if ($panel->getId() === 'workshop_supervisor') return $this->hasRole('Workshop Supervisor'); // <== إضافة هذا الشرط
         if ($panel->getId() === 'investor') return $this->hasRole('Investor'); // <== إضافة هذا الشرط
         if ($panel->getId() === 'reviewer') return $this->hasRole('Reviewer'); // <== إضافة هذا الشرط
         if ($panel->getId() === 'worker') return $this->hasRole('Worker'); // <== إضافة هذا الشرط


        if ($panel->getId() === 'engineer') { // <== منطق لوحة المهندس الموحدة
        $engineerRoles = [
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer'
        ];
        foreach ($engineerRoles as $roleName) {
            if ($this->hasRole($roleName)) return true;
        }
        return false;
    }


         if ($panel->getId() === 'service_proposer') {
        // إذا كان هناك دور 'Service Proposer' محدد:
        // return $this->hasRole('Service Proposer');
        // أو، إذا كان أي مستخدم ليس له دور إداري/متخصص يمكن أن يكون مقدم خدمة:
        return !$this->hasRole('Admin') && !$this->hasRole('Manager') && !$this->hasRole('Workshop Supervisor') && !$this->hasRole('Reviewer');
    }   

        return false;
    }

    public function cvs()
    {
        return $this->hasMany(Cv::class);
    }

    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'manager_user_id');
    }

    public function projectInvestorLinks()
    {
        return $this->hasMany(ProjectInvestorLink::class, 'investor_user_id');
    }

    public function workerWorkshopLinks()
    {
        return $this->hasMany(WorkerWorkshopLink::class, 'worker_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'employee_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function proposedServices()
    {
        return $this->hasMany(NewServiceProposal::class, 'user_id');
    }

    public function reviewedServiceProposals()
    {
        return $this->hasMany(NewServiceProposal::class, 'reviewer_user_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to_user_id');
    }
}