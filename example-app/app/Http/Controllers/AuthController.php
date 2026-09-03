<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * 憨憨专属摸鱼网站 - 登录 / 注册 / 退出控制器
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * 展示注册表单（已登录用户跳回首页）
     *
     * GET /register
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.register');
    }

    /**
     * 处理注册
     *
     * POST /register
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:30'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'signature'=> ['nullable', 'string', 'max:200'],
        ], [
            'name.required'      => '请给自己起个摸鱼昵称',
            'email.required'     => '请填写邮箱',
            'email.unique'       => '该邮箱已被注册啦',
            'password.required'  => '请设置密码',
            'password.confirmed' => '两次输入的密码不一致',
            'password.min'       => '密码至少需要 6 个字符',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'nickname'  => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'signature' => $data['signature'] ?? '今天也是快乐摸鱼的一天~',
        ]);

        Auth::login($user);
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('home')
            ->with('success', '注册成功，欢迎加入憨憨摸鱼俱乐部！');
    }

    /**
     * 展示登录表单（已登录用户跳回首页）
     *
     * GET /login
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /**
     * 处理登录
     *
     * POST /login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => '请填写邮箱',
            'password.required' => '请填写密码',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->forceFill(['last_login_at' => now()])->save();

            return redirect()->intended(route('home'))
                ->with('success', '登录成功，欢迎回来，' . $user->name . '！');
        }

        return back()
            ->withErrors(['email' => '邮箱或密码不正确，再试一次吧'])
            ->onlyInput('email');
    }

    /**
     * 处理退出登录
     *
     * POST /logout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', '已安全退出，摸鱼记录已清除~');
    }
}
