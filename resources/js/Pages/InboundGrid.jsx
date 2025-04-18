import { useState } from "react";
import axios from "axios";

export default function InboundGrid({ inbounds }) {
    if (inbounds.length === 0) {
        return (
            <div className="text-center text-gray-600 dark:text-gray-300 col-span-full flex flex-col items-center justify-center min-h-[200px]">
                <h4 className="text-lg font-semibold mb-2">
                    Welcome to Media Associate
                </h4>
                <p className="mb-1">
                    To get started, upload a PDF through the capture interface.
                </p>
                <p>Once uploaded, your summarized inbounds will appear here.</p>
                <p>
                    API: POST Form Request to /api/inbounds Fields: user_id,
                    notes, pdf
                </p>
            </div>
        );
    }

    return (
        <>
            {inbounds
                .slice()
                .reverse()
                .map((item) => {
                    const [summary, setSummary] = useState(item.summary);
                    const [isRegenerating, setIsRegenerating] = useState(false);
                    const [isEditingSource, setIsEditingSource] =
                        useState(false);
                    const [url, setUrl] = useState(item.url || "");
                    const [source, setSource] = useState(item.source || "");

                    const handleSave = async () => {
                        try {
                            await axios.put(`api/inbounds/${item.id}`, {
                                summary,
                            });
                            alert("Saved!");
                        } catch (err) {
                            console.error(err);
                            alert("Error saving summary.");
                        }
                    };

                    const handleRegenerate = async () => {
                        try {
                            setIsRegenerating(true);
                            const response = await axios.post(
                                `api/inbounds/${item.id}/regenerate`
                            );
                            setSummary(response.data.summary);
                            alert("Summary regenerated!");
                        } catch (err) {
                            console.error(err);
                            alert("Error regenerating summary.");
                        } finally {
                            setIsRegenerating(false);
                        }
                    };

                    const handleDelete = async () => {
                        if (
                            !confirm(
                                "Are you sure you want to delete this inbound?"
                            )
                        )
                            return;
                        try {
                            await axios.delete(`api/inbounds/${item.id}`);
                            window.location.reload();
                        } catch (err) {
                            console.error(err);
                            alert("Error deleting inbound.");
                        }
                    };

                    const handleSourceUpdate = async () => {
                        try {
                            await axios.put(`api/inbounds/${item.id}`, { url });
                            window.location.reload();
                        } catch (err) {
                            console.error(err);
                            alert("Error updating source.");
                        }
                    };

                    return (
                        <div
                            key={item.id}
                            className="flex flex-col p-4 border rounded-lg shadow-sm bg-white dark:bg-gray-800 hover:shadow-md transition-shadow w-full"
                        >
                            <div className="mb-2 flex items-center justify-between">
                                <div>
                                    <span className="font-medium">
                                        Source:{" "}
                                    </span>
                                    {isEditingSource ? (
                                        <input
                                            type="text"
                                            value={url}
                                            onChange={(e) =>
                                                setUrl(e.target.value)
                                            }
                                            className="px-2 py-1 border rounded text-sm dark:bg-gray-900 dark:text-white"
                                        />
                                    ) : (
                                        <a
                                            href={item.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-blue-400 underline"
                                        >
                                            {item.source}
                                        </a>
                                    )}
                                </div>
                                <div className="ml-2">
                                    {isEditingSource ? (
                                        <button
                                            onClick={handleSourceUpdate}
                                            className="bg-white text-green-600 hover:text-green-800 border border-gray-300 px-2 py-1 rounded text-base"
                                            title="Save"
                                        >
                                            ✔
                                        </button>
                                    ) : (
                                        <button
                                            onClick={() =>
                                                setIsEditingSource(true)
                                            }
                                            className="bg-white text-gray-600 hover:text-gray-800 border border-gray-300 px-2 py-1 rounded text-base"
                                            title="Edit Source"
                                        >
                                            Edit
                                        </button>
                                    )}
                                </div>
                            </div>
                            <textarea
                                value={summary}
                                onChange={(e) => setSummary(e.target.value)}
                                className="text-sm text-gray-700 dark:text-gray-200 dark:bg-gray-900 p-2 border rounded resize-none mb-2"
                                rows={8}
                            />
                            <div className="flex justify-end space-x-2 mt-2">
                                <button
                                    type="button"
                                    onClick={handleRegenerate}
                                    title="Regenerate Summary"
                                    className="text-white bg-green-600 hover:bg-green-700 px-4 py-1 rounded disabled:opacity-50"
                                    disabled={isRegenerating}
                                >
                                    {isRegenerating ? "⏳" : "↻"}
                                </button>
                                <button
                                    type="button"
                                    onClick={handleDelete}
                                    title="Delete"
                                    className="text-white bg-red-600 hover:bg-red-700 px-4 py-1 rounded"
                                >
                                    ⛌
                                </button>
                                <button
                                    type="button"
                                    onClick={handleSave}
                                    className="text-white bg-blue-600 hover:bg-blue-700 px-4 py-1 rounded"
                                >
                                    Save
                                </button>
                            </div>
                        </div>
                    );
                })}
        </>
    );
}
