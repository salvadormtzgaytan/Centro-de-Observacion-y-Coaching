<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Bell extends Component
{
    public int $limit = 10;

    public function getUser()
    {
        return Auth::user();
    }

    public function markAsRead(string $id): void
    {
        $n = $this->getUser()->notifications()->whereKey($id)->first();
        if ($n && is_null($n->read_at)) {
            $n->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $this->getUser()->unreadNotifications->markAsRead();
    }

    public function goTo(string $id)
    {
        $n = $this->getUser()->notifications()->whereKey($id)->first();
        if ($n) {
            $n->markAsRead();
            $url = data_get($n->data, 'url');
            if ($url) return redirect()->to($url);
        }
    }

    public function render()
    {
        $user = $this->getUser();

        return view('livewire.notifications.bell', [
            'unreadCount'   => $user->unreadNotifications()->count(),
            'latest'        => $user->notifications()->latest()->limit($this->limit)->get(),
        ]);
    }
}

