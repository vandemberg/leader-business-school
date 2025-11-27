import React, { useState } from "react";
import { Link } from "@inertiajs/react";
import images from "../../utils/images";

interface PremiumCourseCardProps {
    course: {
        id: number;
        title: string;
        description: string;
        thumbnail: string;
        progress: number;
    };
}

function selectThumbnail(course: any) {
    if (course.thumbnail.includes("http")) {
        return course.thumbnail;
    }
    return images[course.thumbnail as keyof typeof images] || course.thumbnail;
}

const PremiumCourseCard: React.FC<PremiumCourseCardProps> = ({ course }) => {
    const [isHovered, setIsHovered] = useState(false);

    const getStatusBadge = () => {
        if (course.progress === 100) {
            return { text: "CONCLUÍDO", className: "bg-green-500/80" };
        } else if (course.progress > 0) {
            return { text: "EM ANDAMENTO", className: "bg-primary/80" };
        } else {
            return { text: "NOVO", className: "bg-gray-500/80" };
        }
    };

    const statusBadge = getStatusBadge();
    const thumbnail = selectThumbnail(course);

    return (
        <div
            className="group relative flex flex-col overflow-hidden rounded-xl border border-white/10 bg-surface-dark transition-all duration-300 hover:border-primary/50 hover:shadow-2xl hover:shadow-primary/20"
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
        >
            {/* Image */}
            <div className="relative">
                <div
                    className="aspect-video w-full bg-cover bg-center"
                    style={{ backgroundImage: `url(${thumbnail})` }}
                ></div>
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <span
                    className={`absolute top-3 right-3 ${statusBadge.className} text-white text-xs font-bold px-2 py-1 rounded-md`}
                >
                    {statusBadge.text}
                </span>
            </div>

            {/* Content */}
            <div className="flex flex-col p-5 flex-grow">
                <h3 className="text-xl font-bold font-heading text-white mb-2">
                    {course.title}
                </h3>
                <p className="text-sm text-[#A0A0A0] leading-relaxed line-clamp-2">
                    {course.description}
                </p>
            </div>

            {/* Progress and Button */}
            <div className="p-5 pt-0">
                <div className="w-full rounded-full bg-white/10 h-1.5 mb-4">
                    <div
                        className={`h-1.5 rounded-full ${
                            course.progress === 100 ? "bg-green-500" : "bg-primary"
                        }`}
                        style={{ width: `${course.progress || 0}%` }}
                    ></div>
                </div>
                <Link
                    href={`/courses/${course.id}/watch`}
                    className={`w-full flex items-center justify-center gap-2 h-11 rounded-lg text-white font-bold text-sm transition-opacity ${
                        course.progress === 100
                            ? "bg-white/10 hover:bg-white/20"
                            : "gradient-button hover:opacity-90"
                    }`}
                >
                    <span className="material-symbols-outlined">
                        {course.progress === 100 ? "refresh" : "play_circle"}
                    </span>
                    {course.progress === 100
                        ? "Revisar Curso"
                        : course.progress > 0
                        ? "Continuar Curso"
                        : "Iniciar Curso"}
                </Link>
            </div>

            {/* Hover Overlay */}
            <div
                className={`absolute inset-0 bg-surface-dark/95 backdrop-blur-sm p-5 transition-opacity duration-300 flex flex-col justify-center ${
                    isHovered ? "opacity-100" : "opacity-0"
                }`}
            >
                <h3 className="text-xl font-bold font-heading text-white mb-2">
                    {course.title}
                </h3>
                <p className="text-sm text-[#A0A0A0] leading-relaxed">
                    {course.description}
                </p>
            </div>
        </div>
    );
};

export { PremiumCourseCard };

