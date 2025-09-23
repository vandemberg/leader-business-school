import React from "react";
import images from "../../utils/images";
import { Link } from "@inertiajs/react";

interface CourseCardProps {
    course: {
        id: number;
        title: string;
        description: string;
        thumbnail: string;
        icon: string;
        progress: number;
    }
}

function selectThumbsnail(course: any) {
    if (course.thumbnail.includes('http')) {
        console.log(course.thumbnail);
        return course.thumbnail;
    }

    return images[course.thumbnail as keyof typeof images];
}

const CourseCard: React.FC<CourseCardProps> = ({ course }) => {
    return (
        <div className="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200 hover:border-blue-300 overflow-hidden h-full flex flex-col min-h-[500px]">
            <div className="w-full">
                <div className="w-full h-72 flex items-center overflow-hidden bg-cover bg-center" style={{ backgroundImage: `url(${selectThumbsnail(course)})` }}>
                </div>

                <div className="p-6 flex-1 flex flex-col">
                    <h2 className="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                        {course.title}
                    </h2>

                    <p className="text-gray-600 text-sm mb-6 flex-1 line-clamp-3">
                        {course.description}
                    </p>

                    <div className="mt-auto space-y-4">
                        <div className="flex items-center justify-between text-sm text-gray-600">
                            <span>Progresso</span>
                            <span className="font-semibold">{course.progress || 0}% Concluído</span>
                        </div>

                        <div className="w-full bg-gray-200 rounded-full h-2">
                            <div
                                className="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                                style={{ width: `${course.progress || 0}%` }}
                            ></div>
                        </div>

                        <div className="flex justify-center">
                            <Link
                                href={`/courses/${course.id}/watch`}
                                className="w-full max-w-xs bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition-all duration-300 transform hover:scale-105"
                            >
                                Assistir o curso
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export { CourseCard };
