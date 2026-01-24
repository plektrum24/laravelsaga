@extends('layouts.app')

@section('title', 'Profile | SAGA POS')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Profile</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Manage your profile and account settings</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Profile Card -->
        <x-card.card class="lg:col-span-1">
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <div class="w-24 h-24 rounded-full bg-brand-500 flex items-center justify-center text-white text-3xl font-bold">
                        JD
                    </div>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                <button class="mt-4 w-full rounded-lg bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">
                    Upload Photo
                </button>
            </div>
        </x-card.card>

        <!-- Profile Form -->
        <div class="lg:col-span-2 space-y-6">
            <x-card.card title="Personal Information">
                <form class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <x-form.input label="First Name" value="John" />
                        <x-form.input label="Last Name" value="Doe" />
                        <x-form.input label="Email" type="email" value="{{ auth()->user()->email }}" />
                        <x-form.input label="Phone" value="+62 812 3456 7890" />
                    </div>

                    <div class="flex gap-4">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                            Save Changes
                        </button>
                    </div>
                </form>
            </x-card.card>

            <x-card.card title="Change Password">
                <form class="space-y-6">
                    <x-form.input label="Current Password" type="password" />
                    <x-form.input label="New Password" type="password" />
                    <x-form.input label="Confirm Password" type="password" />

                    <div class="flex gap-4">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                            Update Password
                        </button>
                    </div>
                </form>
            </x-card.card>
        </div>
    </div>
@endsection
