# 🐛 Login Bug Fix

## The Problem

After logging in and going back to the **home page**, clicking "Login" again didn't work.

**Why?**

The `/login` page has a rule: *"if you're already logged in, don't show this page."*

When a logged-in user tried to visit `/login`, Laravel needed to redirect them somewhere else. But no valid destination was set, so it sent them back to `/` (the home page) — causing an **infinite loop**.

```
Home → click Login → blocked → redirected back to Home → stuck 🔁
```

---

## The Fix

We told Laravel exactly where to send a logged-in user who tries to visit `/login`:

- **Super Admin** → `/admin/dashboard`
- **Teacher** → `/teacher/dashboard`
- **Student** → `/student/dashboard`

**File changed:** `bootstrap/app.php`

```php
RedirectIfAuthenticated::redirectUsing(function ($request) {
    $user = $request->user();

    if ($user?->hasRole('super_admin')) return '/admin/dashboard';
    if ($user?->hasRole('teacher'))     return '/teacher/dashboard';

    return '/student/dashboard';
});
```

Now if you're already logged in and visit `/login`, you go straight to your dashboard. ✅  
If you're not logged in, the login form shows normally. ✅
