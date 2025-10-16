import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import React from "react";
import YouTube from "react-youtube";
import { Head, router } from "@inertiajs/react";
import axios from "axios";
import "./index.css";
import { secondsToString } from "../../utils/seconds-to-string";

interface CourseProps {
    videos: any[];
    currentVideo: {
        id: number;
        title: string;
        description: string;
        url: string;
    };
    course: {
        id: number;
        title: string;
        description: string;
        thumbnail: string;
        icon: string;
    };
    auth: any;
}

function formatTextToUrl(url: string) {
    let newUrl = url;
    if (!url.startsWith("http")) {
        newUrl = `https://${url}`;
    }

    return `<a href="${newUrl}" target="_blank" rel="noopener noreferrer">${newUrl}</a>`;
}

function urlMatch(text: string, url: string) {
    return text.includes(url) ? formatTextToUrl(url) : text;
}

function parseDescription(description: string) {
    return description
        .split("\n")
        .map((line) => {
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            return line.replace(urlRegex, (url) => urlMatch(line, url));
        })
        .join("\n");
}

const Course: React.FC<CourseProps> = ({
    auth,
    course,
    currentVideo,
    videos,
}) => {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center text-gray-800">
                    <h2 className="font-semibold text-xl  leading-tight flex items-center">
                        {course.title}
                    </h2>
                </div>
            }
        >
            <Head title={course.title} />
            <p className="m-6 mx-6 text-xltext-bold text-gray-800">
                {currentVideo.title}
            </p>

            <div className="flex flex-1 bg-whitem-6 p-6 gap-6 max-h-[600px]">
                <div className="flex w-3/4 h-[560px]">
                    <YouTube
                        videoId={currentVideo.url}
                        onEnd={() => {
                            axios
                                .post(`/videos/${currentVideo.id}/complete`)
                                .then(() =>
                                    router.visit(`/courses/${course.id}/watch`)
                                );
                        }}
                        style={{
                            width: "100%",
                            height: "100%",
                        }}
                        opts={{
                            height: "100%",
                            width: "100%",
                            playerVars: {
                                autoplay: 0,
                                modestbranding: 1,
                            },
                        }}
                    />
                </div>

                <div className="w-1/4 bg-gray-100 rounded-lg p-2 border-gray-800 border-solid border-x border-y overflow-scroll max-h-[600px]">
                    <div className="flex flex-col items-center gap-2 p-2">
                        <span className="text-gray-800 font-bold">
                            {" "}
                            Conteúdos{" "}
                        </span>

                        {videos.map((video) => (
                            <a
                                href={`/courses/${course.id}/videos/${video.id}`}
                                key={video.id}
                                className={`w-full h-1/6 bg-gray-200 flex rounded-sm p-2 flex-col cursor-pointer ${
                                    video.watched ? "watched" : "not-watched"
                                } `}
                            >
                                <div className="flex items-center gap-4">
                                    <i className="bx bxs-videos"></i>
                                    <span>{video.title}</span>
                                </div>

                                {video.time_in_seconds > 0 && (
                                    <span className="w-full text-right text-sm pl-4 text-gray-500 mt-2">
                                        {secondsToString(video.time_in_seconds)}
                                    </span>
                                )}
                            </a>
                        ))}
                    </div>
                </div>
            </div>

            <div className="w-3/4 p-2 max-h-[600px] overflow-y-auto">
                <div className="pl-12 text-justify">
                    {parseDescription(currentVideo.description)}
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Course;
