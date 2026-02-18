<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BarChart3, Users, FileText, Shield, Building2 } from 'lucide-vue-next';
import { dashboard, login } from '@/routes';
</script>

<template>
    <Head title="Mister Accountant - CRM for Accountants" />

    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white dark:from-slate-950 dark:to-slate-900">
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/80 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-950/80">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                <Link href="/" class="flex items-center gap-2 font-semibold text-slate-800 dark:text-white">
                    <Building2 class="h-8 w-8 text-emerald-600 dark:text-emerald-500" />
                    <span>Mister Accountant</span>
                </Link>
                <nav class="flex items-center gap-3">
                    <template v-if="$page.props.auth?.user">
                        <Link
                            :href="dashboard()"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                        >
                            Login
                        </Link>
                        <Link
                            href="/plans"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                        >
                            Sign up
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
            <section class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                    CRM built for
                    <span class="text-emerald-600 dark:text-emerald-400">accountants</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
                    Manage clients, invoices, tasks and your team in one place. Multi-tenant, plan-based access and permissions — start with a plan that fits you.
                </p>
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <Link
                        href="/plans"
                        class="inline-flex items-center rounded-xl bg-emerald-600 px-6 py-3 text-base font-semibold text-white shadow-lg transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                    >
                        Sign up — Choose a plan
                    </Link>
                    <Link
                        :href="login()"
                        class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        Login
                    </Link>
                </div>
            </section>

            <!-- Features -->
            <section class="mt-24 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <Users class="h-6 w-6" />
                    </div>
                    <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Client Management</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Keep all client data and contacts in one place, per organization.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <FileText class="h-6 w-6" />
                    </div>
                    <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Invoice & Billing</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Create and track invoices and payments with plan-based limits.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <BarChart3 class="h-6 w-6" />
                    </div>
                    <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Reports</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Reports and analytics based on your subscription plan.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <Shield class="h-6 w-6" />
                    </div>
                    <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Roles & Permissions</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Owner, Admin, Staff and Client roles with module-based access.
                    </p>
                </div>
            </section>

            <!-- CTA -->
            <section class="mt-24 rounded-3xl border border-slate-200 bg-slate-100/80 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-800/50 sm:py-16">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">
                    Ready to get started?
                </h2>
                <p class="mx-auto mt-3 max-w-xl text-slate-600 dark:text-slate-400">
                    Pick a plan, sign up or log in, and start managing your practice.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <Link
                        href="/plans"
                        class="inline-flex rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                    >
                        Sign up
                    </Link>
                    <Link
                        :href="login()"
                        class="inline-flex rounded-xl border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        Login
                    </Link>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 py-8 dark:border-slate-800">
            <div class="mx-auto max-w-6xl px-4 text-center text-sm text-slate-500 dark:text-slate-400">
                &copy; {{ new Date().getFullYear() }} Mister Accountant. Multi-tenant CRM for accountants.
            </div>
        </footer>
    </div>
</template>
