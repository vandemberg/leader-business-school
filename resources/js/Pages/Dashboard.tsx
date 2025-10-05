import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import { DashboardCourseCard } from "@/components/courses/DashboardCourseCard";

interface Course {
    id: number;
    title: string;
    description: string;
    thumbnail: string;
}

interface DashboardProps {
    auth: any;
    coursesInProgress: Course[];
    updatedCourses: Course[];
    platform: PlatformProp;
}

interface PlatformProp {
    id: number;
    name: string;
    slug: string;
}

const Dashboard: React.FC<DashboardProps> = ({ auth, coursesInProgress, updatedCourses, platform }) => {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800  leading-tight">
                    {platform.name}
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-12">
                        <div className="flex items-center justify-between mb-6">
                            <h3 className="text-2xl font-bold text-gray-900">
                                Cursos em Progresso
                            </h3>
                            <Link
                                href={route("courses.index")}
                                className="text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Ver todos os cursos →
                            </Link>
                        </div>

                        {coursesInProgress.length === 0 ? (
                            <div className="bg-gray-50 rounded-lg p-8 text-center">
                                <p className="text-gray-500 text-lg">
                                    Nenhum curso em progresso no momento.
                                </p>
                                <Link
                                    href={route("courses.index")}
                                    className="mt-4 inline-block bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                >
                                    Explorar Cursos
                                </Link>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                {coursesInProgress.map((course: any) => (
                                    <DashboardCourseCard
                                        key={course.id}
                                        course={course}
                                    />
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Cursos Atualizados */}
                    <div>
                        <div className="flex items-center justify-between mb-6">
                            <h3 className="text-2xl font-bold text-gray-900">
                                Cursos Atualizados
                            </h3>
                            <Link
                                href={route("courses.index")}
                                className="text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Ver todos os cursos →
                            </Link>
                        </div>

                        {updatedCourses?.length === 0 ? (
                            <div className="bg-gray-50 rounded-lg p-8 text-center">
                                <p className="text-gray-500 text-lg">
                                    Nenhum curso novo disponível.
                                </p>
                                <Link
                                    href={route("courses.index")}
                                    className="mt-4 inline-block bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                >
                                    Explorar Cursos
                                </Link>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                {updatedCourses?.map((course: any) => (
                                    <DashboardCourseCard
                                        key={course.id}
                                        course={course}
                                    />
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Dashboard;
