<?php

namespace App\Modules\Log\Livewire;

use App\Modules\Log\Models\Activity;
use App\Modules\Auth\Traits\Authorize;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;
use Livewire\Component;
use Zofe\Rapyd\Traits\WithDataTable;

class LogUsersTable extends Component
{
    use Authorize;
    use WithDataTable;

    public $search;
    public $log_name;
    public $user;
    public $date_from;
    public $date_to;
    public $exclude = false;
    public $timezone = 'Europe/Rome';

    public $users = [];
    public $log_names = [];

    public function booted()
    {
        $this->authorize('admin');
    }

    public function mount()
    {
        $userModel       = config('auth.providers.users.model', \Illuminate\Foundation\Auth\User::class);
        $this->users     = $userModel::all()->pluck('name', 'id')->toArray();
        $this->log_names = Arr::except(__('log::title'), []);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedExclude()
    {
        $this->resetPage();
    }

    #[On('userTimezone')]
    public function userTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    protected function getDataset($paginate = true)
    {
        $items = Activity::query()->where(function ($q) {
            $q->where('log_name', 'like', '%' . $this->search . '%')
                ->orWhere('properties', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
        });

        if ($this->exclude) {
            $items->whereNotNull('causer_type')->whereNotNull('causer_id');
        }

        if ($this->log_name) {
            foreach ($this->log_name as $key => $name) {
                $key === 0
                    ? $items->where('log_name', 'like', $name . '%')
                    : $items->orWhere('log_name', 'like', $name . '%');
            }
        }

        if ($this->date_from) {
            $items->where('created_at', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $items->where('created_at', '<=', Carbon::parse($this->date_to)->endOfDay());
        }

        if ($this->user) {
            $items->whereIn('causer_id', $this->user);
        }

        if (!$paginate) {
            return $items->get();
        }

        return $items->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->getDataset();

        return view('log::log_users_table', compact('items'))
            ->layout('log::admin');
    }
}
