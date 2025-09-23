import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { CourseCard } from "@/components/courses/CourseCard";

interface Course {
    id: number;
    title: string;
    description: string;
    thumbnail: string;
    icon: string;
    progress: number;
}

interface CoursesIndexProps {
    auth: any;
    courses: Course[];
}

const CoursesIndex: React.FC<CoursesIndexProps> = ({ auth, courses }) => {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Catálogo de Cursos
                </h2>
            }
        >
            <Head title="Cursos" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <h1 className="text-4xl font-bold text-gray-900 mb-4">
                            Todos os Cursos Disponíveis
                        </h1>
                        <p className="text-lg text-gray-600 max-w-3xl">
                            Explore nossa coleção completa de cursos e continue sua jornada de aprendizado.
                        </p>
                    </div>

                    {courses.length === 0 ? (
                        <div className="text-center py-16 bg-white rounded-xl shadow-sm">
                            <div className="text-gray-500 text-xl">
                                Nenhum curso disponível no momento.
                            </div>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-8 items-stretch">
                            {courses.map((course: Course) => (
                                <CourseCard key={course.id} course={course} />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default CoursesIndex;
