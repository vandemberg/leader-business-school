import React from "react";
import images from "../../utils/images";
import { Link } from "@inertiajs/react";

interface DashboardCourseCardProps {
    course: {
        id: number;
        title: string;
        description: string;
        thumbnail: string;
        icon: string;
        progress: number;
    };
}

function selectThumbsnail(course: any) {
    if (course.thumbnail.includes("http")) {
        return course.thumbnail;
    }
    return images[course.thumbnail as keyof typeof images];
}

const DashboardCourseCard: React.FC<DashboardCourseCardProps> = ({
    course,
}) => {
    return (
        <div className="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-blue-300 overflow-hidden h-full flex flex-col">
            {/* Image */}
            <div
                className="w-full h-48 flex items-center overflow-hidden bg-cover bg-center"
                style={{ backgroundImage: `url(${selectThumbsnail(course)})` }}
            ></div>

            {/* Content */}
            <div className="p-4 flex-1 flex flex-col">
                <h3 className="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 min-h-[3.5rem]">
                    {course.title}
                </h3>

                <p className="text-gray-600 text-sm mb-4 flex-1 line-clamp-2">
                    {course.description}
                </p>

                <div className="space-y-3">
                    <div className="flex items-center justify-between text-xs text-gray-600">
                        <span>Progresso</span>
                        <span className="font-medium">
                            {course.progress || 0}%
                        </span>
                    </div>

                    <div className="w-full bg-gray-200 rounded-full h-2">
                        <div
                            className="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                            style={{ width: `${course.progress || 0}%` }}
                        ></div>
                    </div>
                </div>

                <Link
                    href={`/courses/${course.id}/watch`}
                    className="w-full mt-4 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-3 rounded-md text-center transition-colors duration-200"
                >
                    Continuar Curso
                </Link>
            </div>
        </div>
    );
};

export { DashboardCourseCard };
