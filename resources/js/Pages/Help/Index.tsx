import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";

interface HelpCategory {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    description: string | null;
    articles_count: number;
}

interface HelpArticle {
    id: number;
    question: string;
    answer: string;
    category_id: number;
    is_faq: boolean;
    views_count: number;
    category?: HelpCategory;
}

interface HelpIndexProps {
    auth: any;
    categories: HelpCategory[];
    faqs: HelpArticle[];
    articles: HelpArticle[];
    filters: {
        search: string;
        category: string;
    };
}

const HelpIndex: React.FC<HelpIndexProps> = ({
    auth,
    categories,
    faqs,
    articles,
    filters,
}) => {
    const [search, setSearch] = useState(filters.search || "");
    const [expandedFaqs, setExpandedFaqs] = useState<number[]>([]);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route("help.index"), { search }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const toggleFaq = (faqId: number) => {
        setExpandedFaqs((prev) =>
            prev.includes(faqId) ? prev.filter((id) => id !== faqId) : [...prev, faqId]
        );
    };

    const handleCategoryClick = (categorySlug: string) => {
        router.get(route("help.index"), { category: categorySlug }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const categoryIcons: Record<string, string> = {
        "Conta e Perfil": "person",
        "Cursos e Aulas": "school",
        "Pagamentos": "credit_card",
        "Certificados": "workspace_premium",
        "Problemas Técnicos": "build",
        "Comunidade": "diversity_3",
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Ajuda" />

            <div className="layout-content-container flex flex-col w-full max-w-5xl flex-1 gap-12">
                {/* Header Section */}
                <section className="text-center">
                    <h1 className="text-white text-4xl md:text-5xl font-black leading-tight tracking-[-0.033em] font-heading mb-4">
                        Como podemos ajudar?
                    </h1>
                    <p className="text-[#A0A0A0] text-lg max-w-2xl mx-auto mb-8">
                        Tem alguma dúvida? Encontre respostas em nosso FAQ ou entre em contato com nosso suporte.
                    </p>
                    <form onSubmit={handleSearch} className="relative max-w-2xl mx-auto">
                        <span className="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-white/50 text-2xl">
                            search
                        </span>
                        <input
                            type="search"
                            className="w-full bg-surface-dark border border-white/10 rounded-full h-14 pl-14 pr-6 text-base text-white focus:ring-2 focus:ring-primary focus:border-primary placeholder:text-white/40"
                            placeholder="Digite sua pergunta aqui..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </form>
                </section>

                {/* Categories Section */}
                <section>
                    <h2 className="text-white text-2xl font-bold font-heading mb-6 text-center sm:text-left">
                        Navegue por tópicos
                    </h2>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {categories.map((category) => {
                            const iconName = category.icon || categoryIcons[category.name] || "help";
                            return (
                                <Link
                                    key={category.id}
                                    href={route("help.categories.show", category.slug)}
                                    className="group flex items-center gap-4 rounded-xl bg-surface-dark p-6 border border-white/10 hover:border-primary transition-colors hover:bg-surface-dark/80"
                                >
                                    <div className="flex-shrink-0 flex items-center justify-center size-12 rounded-lg bg-gradient-to-br from-secondary to-primary text-white">
                                        <span className="material-symbols-outlined text-3xl">{iconName}</span>
                                    </div>
                                    <div>
                                        <h3 className="text-white font-bold">{category.name}</h3>
                                        <p className="text-sm text-white/60">
                                            {category.articles_count} {category.articles_count === 1 ? "artigo" : "artigos"}
                                        </p>
                                    </div>
                                </Link>
                            );
                        })}
                    </div>
                </section>

                {/* FAQ Section */}
                <section>
                    <h2 className="text-white text-2xl font-bold font-heading mb-6 text-center sm:text-left">
                        Perguntas Frequentes
                    </h2>
                    <div className="space-y-4">
                        {faqs.length > 0 ? (
                            faqs.map((faq) => (
                                <details
                                    key={faq.id}
                                    className="group rounded-lg bg-surface-dark border border-white/10 overflow-hidden"
                                    open={expandedFaqs.includes(faq.id)}
                                >
                                    <summary
                                        onClick={(e) => {
                                            e.preventDefault();
                                            toggleFaq(faq.id);
                                        }}
                                        className="flex items-center justify-between p-5 cursor-pointer list-none"
                                    >
                                        <span className="font-medium text-white">{faq.question}</span>
                                        <span
                                            className={`material-symbols-outlined text-white/70 transition-transform duration-300 ${
                                                expandedFaqs.includes(faq.id) ? "rotate-180" : ""
                                            }`}
                                        >
                                            expand_more
                                        </span>
                                    </summary>
                                    {expandedFaqs.includes(faq.id) && (
                                        <div className="px-5 pb-5 text-white/70">{faq.answer}</div>
                                    )}
                                </details>
                            ))
                        ) : (
                            <div className="text-center py-12 text-[#A0A0A0]">
                                Nenhuma pergunta frequente encontrada.
                            </div>
                        )}
                    </div>
                </section>

                {/* Contact Section */}
                <section>
                    <div className="rounded-xl bg-gradient-to-r from-primary to-secondary p-8 md:p-12 text-center">
                        <h2 className="text-white text-3xl font-bold font-heading mb-3">
                            Não encontrou o que procurava?
                        </h2>
                        <p className="text-white/80 max-w-xl mx-auto mb-8">
                            Nossa equipe de suporte está pronta para te ajudar. Entre em contato conosco e retornaremos o mais breve possível.
                        </p>
                        <button className="h-12 px-8 rounded-lg bg-white text-primary font-bold text-sm transition-transform hover:scale-105">
                            Entrar em Contato
                        </button>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
};

export default HelpIndex;

