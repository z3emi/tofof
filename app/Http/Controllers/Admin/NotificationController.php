<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $status = $request->string('status')->lower()->value();
        if (!in_array($status, ['all', 'unread', 'read'], true)) {
            $status = 'all';
        }

        $notificationsQuery = $admin->notifications()->latest();

        if ($status === 'unread') {
            $notificationsQuery->whereNull('read_at');
        } elseif ($status === 'read') {
            $notificationsQuery->whereNotNull('read_at');
        }

        $notifications = $notificationsQuery->paginate(20)->withQueryString();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'status' => $status,
            'unreadCount' => $admin->unreadNotifications()->count(),
        ]);
    }

    public function feed()
    {
        $admin = Auth::guard('admin')->user();
        $notifications = $admin->notifications()->latest()->take(15)->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $admin->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $notificationId = $request->input('id');

        $notification = $notificationId
            ? $admin->notifications()->find($notificationId)
            : null;

        $expectsJson = $request->expectsJson() || $request->isJson();

        if ($notification) {
            $notification->markAsRead();

            if ($expectsJson) {
                return response()->json(['success' => true]);
            }

            return back()->with('status', 'تم تعليم الإشعار كمقروء.');
        }

        if ($expectsJson) {
            return response()->json(['success' => false], 404);
        }

        return back()->withErrors(['notification' => 'الإشعار المطلوب غير موجود.']);
    }

    public function markAllAsRead(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $admin->unreadNotifications->markAsRead();

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'تم تعليم جميع الإشعارات كمقروءة.');
    }
}

