<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, \Spatie\Permission\Traits\HasRoles;

    /**
     * Get the contacts for the user.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'odoo_user_id',
        'odoo_salesperson_id',
        'sales_manager_id',
        'team_id',
        'self_gen_base',
        'company_lead_base',
        'commission_split',
        'is_active',
        'legacy_id',
        'current_login_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'self_gen_base' => 'decimal:2',
            'company_lead_base' => 'decimal:2',
            'commission_split' => 'decimal:2',
            'is_active' => 'boolean',
            'current_login_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }



    /**
     * Get the team for this user.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the sales manager for this user.
     */
    public function salesManager()
    {
        return $this->belongsTo(User::class, 'sales_manager_id');
    }

    /**
     * Get the team members if this user is a team manager.
     */
    public function teamMembers()
    {
        return $this->hasMany(User::class, 'sales_manager_id');
    }

    /**
     * Get all commission calculations for this user.
     */
    public function commissionCalculations()
    {
        return $this->hasMany(CommissionCalculation::class);
    }

    /**
     * Get all commission adjustments made by this user.
     */
    public function commissionAdjustments()
    {
        return $this->hasMany(CommissionAdjustment::class, 'adjusted_by');
    }

    /**
     * Get all commission approvals made by this user.
     */
    public function commissionApprovals()
    {
        return $this->hasMany(CommissionApproval::class, 'approver_id');
    }
    /**
     * Get all user IDs in the team hierarchy (recursive).
     * This includes direct reports and team members if this user is a team manager.
     */
    public function getTeamUserIds(): array
    {
        $userIds = [$this->id];
        
        // Get direct reports (users who have this user as sales_manager_id)
        $directReports = User::where('sales_manager_id', $this->id)->get();
        
        foreach ($directReports as $report) {
            // Recursively get their team members
            $userIds = array_merge($userIds, $report->getTeamUserIds());
        }
        
        // Also get team members if user is a team manager (even if not a member of that team)
        $managedTeams = Team::where('team_manager_id', $this->id)->get();
        foreach ($managedTeams as $managedTeam) {
            $teamMemberIds = $managedTeam->members()->pluck('id')->toArray();
            $userIds = array_merge($userIds, $teamMemberIds);
        }
        
        return array_values(array_unique($userIds));
    }
}
