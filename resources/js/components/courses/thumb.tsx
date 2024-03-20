import React from "react";

interface ThumbProps {
    course: {
        id: number;
        title: string;
        description: string;
        thumbnail: string;
        icon: string;
    }
}

const Thumb: React.FC<ThumbProps> = ({ course }) => {
    return (
        <a href={`/courses/${course.id}`} className="p-6 bg-whiteoverflow-hidden sm:rounded-lg flex items-center justify-center flex-col w-1/4 border-2 border-solid border-gray-600 hover:border-gray-400 cursor-pointer" >
            <div className="w-full px-2  text-left">
                <div className="bg-gray-900rounded-full flex items-center" style={{ width: 50, height: 50 }}>
                    <i className={`text-2xl bx ${course.icon} text-gray-800`}></i>
                </div>

                <h2 className="text-gray-900my-4 font-bold">
                    {course.title}
                </h2>

                <p className="text-gray-500">
                    {course.description}
                </p>
            </div>
        </a >
    )
}

export { Thumb };
