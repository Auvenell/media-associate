import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import axios from "axios";

export default function Dashboard({ auth, inbounds = [] }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-8xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            You're logged in!
                        </div>
                        <div className="mt-6 px-5 py-4">
                            <h3 className="text-lg font-bold mb-4">
                                Your Inbounds
                            </h3>
                            <div className="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 bg-gray-50 p-6 rounded-lg">
                                {inbounds.map((item) => {
                                    const [summary, setSummary] = useState(
                                        item.summary
                                    );

                                    const handleSave = async () => {
                                        try {
                                            await axios.put(
                                                `api/inbounds/${item.id}`,
                                                { summary }
                                            );
                                            alert("Saved!");
                                        } catch (err) {
                                            console.error(err);
                                            alert("Error saving summary.");
                                        }
                                    };

                                    return (
                                        <div
                                            key={item.id}
                                            className="flex flex-col p-4 border rounded-lg shadow-sm bg-white hover:shadow-md transition-shadow w-full"
                                        >
                                            <a
                                                href={item.url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-blue-600 underline mb-2"
                                            >
                                                {item.source}
                                            </a>
                                            <textarea
                                                value={summary}
                                                onChange={(e) =>
                                                    setSummary(e.target.value)
                                                }
                                                className="text-sm text-gray-700 p-2 border rounded resize-none mb-2"
                                                rows={4}
                                            />
                                            <button
                                                type="button"
                                                onClick={handleSave}
                                                className="self-end text-white bg-blue-600 hover:bg-blue-700 px-4 py-1 rounded"
                                            >
                                                Save
                                            </button>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
