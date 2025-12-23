import { FormEventHandler } from 'react';
import InputError from '@/components/InputError';
import { Head, Link, useForm } from '@inertiajs/react';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <div className="min-h-screen flex bg-background-dark">
            <Head title="Recuperar Senha" />

            {/* Left side - Background image section */}
            <div className="hidden lg:flex lg:w-2/5 relative overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-br from-purple-900/80 via-blue-900/80 to-purple-800/80">
                    {/* Abstract gradient lines */}
                    <div className="absolute inset-0">
                        <div className="absolute top-0 left-0 w-full h-full">
                            <svg
                                className="w-full h-full"
                                viewBox="0 0 400 800"
                                preserveAspectRatio="none"
                            >
                                <path
                                    d="M0,100 Q200,50 400,150 T400,300 Q200,250 0,350 T0,600 Q200,550 400,650 T400,800"
                                    stroke="url(#gradient1)"
                                    strokeWidth="3"
                                    fill="none"
                                    className="opacity-60"
                                />
                                <path
                                    d="M0,200 Q200,150 400,250 T400,400 Q200,350 0,450 T0,700 Q200,650 400,750"
                                    stroke="url(#gradient2)"
                                    strokeWidth="2"
                                    fill="none"
                                    className="opacity-40"
                                />
                                <defs>
                                    <linearGradient
                                        id="gradient1"
                                        x1="0%"
                                        y1="0%"
                                        x2="100%"
                                        y2="100%"
                                    >
                                        <stop
                                            offset="0%"
                                            stopColor="#EC4899"
                                            stopOpacity="0.8"
                                        />
                                        <stop
                                            offset="100%"
                                            stopColor="#8B5CF6"
                                            stopOpacity="0.4"
                                        />
                                    </linearGradient>
                                    <linearGradient
                                        id="gradient2"
                                        x1="0%"
                                        y1="0%"
                                        x2="100%"
                                        y2="100%"
                                    >
                                        <stop
                                            offset="0%"
                                            stopColor="#3B82F6"
                                            stopOpacity="0.6"
                                        />
                                        <stop
                                            offset="100%"
                                            stopColor="#6366F1"
                                            stopOpacity="0.3"
                                        />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {/* Right side - Form section */}
            <div className="flex-1 lg:w-3/5 flex flex-col justify-center px-6 sm:px-12 lg:px-20 py-12 bg-background-dark">
                <div className="w-full max-w-md mx-auto">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-5xl font-bold text-white mb-2 font-heading">
                            LBS
                        </h1>
                        <p className="text-white/80 text-lg">
                            Leader Business School
                        </p>
                    </div>

                    {/* Welcome message */}
                    <div className="mb-8">
                        <h2 className="text-3xl font-bold text-white mb-2">
                            Recuperar Senha
                        </h2>
                        <p className="text-white/70 text-base">
                            Esqueceu sua senha? Sem problemas. Informe seu endereço de e-mail e enviaremos um link para escolher uma nova senha.
                        </p>
                    </div>

                    {/* Status message */}
                    {status && (
                        <div className="mb-4 font-medium text-sm text-green-400 bg-green-900/20 border border-green-500/30 rounded-lg px-4 py-3">
                            {status}
                        </div>
                    )}

                    {/* Form */}
                    <form onSubmit={submit} className="space-y-6">
                        {/* Email field */}
                        <div>
                            <label
                                htmlFor="email"
                                className="block text-sm font-medium text-white mb-2"
                            >
                                E-mail
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg
                                        className="h-5 w-5 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    className="block w-full pl-12 pr-4 py-3 bg-gray-800/50 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                    placeholder="seuemail@exemplo.com"
                                    autoComplete="username"
                                    autoFocus
                                    onChange={(e) => setData('email', e.target.value)}
                                />
                            </div>
                            <InputError message={errors.email} className="mt-2" />
                        </div>

                        {/* Submit button */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-3 px-4 bg-primary hover:bg-primary/90 text-white font-semibold rounded-lg transition focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-background-dark disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {processing ? "Enviando..." : "Enviar link de recuperação"}
                        </button>

                        {/* Back to login link */}
                        <div className="text-center mt-6">
                            <Link
                                href={route('login')}
                                className="text-sm text-white/70 hover:text-white transition"
                            >
                                ← Voltar para o login
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
