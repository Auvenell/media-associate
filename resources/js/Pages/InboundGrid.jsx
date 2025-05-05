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
                    const [sentiment, setSentiment] = useState(
                        item.metadata?.sentiment || "neutral"
                    );
                    const [marketMover, setMarketMover] = useState(
                        item.metadata?.market_mover || "no"
                    );
                    const [selectedCategories, setSelectedCategories] =
                        useState(item.metadata?.categories || []);
                    const [postTitle, setPostTitle] = useState(
                        item.post_title || ""
                    );
                    const [isEditingTitle, setIsEditingTitle] = useState(false);
                    const toggleCategory = (id) => {
                        setSelectedCategories((prev) =>
                            prev.includes(id)
                                ? prev.filter((catId) => catId !== id)
                                : [...prev, id]
                        );
                    };

                    const handleSave = async () => {
                        try {
                            const response = await axios.put(
                                `api/inbounds/${item.id}`,
                                {
                                    summary,
                                    metadata: {
                                        categories: selectedCategories,
                                        sentiment,
                                        market_mover: marketMover,
                                    },
                                }
                            );

                            // Update local state with the response data
                            if (response.data.metadata) {
                                setSentiment(response.data.metadata.sentiment);
                                setMarketMover(
                                    response.data.metadata.market_mover
                                );
                                setSelectedCategories(
                                    response.data.metadata.categories || []
                                );
                            }

                            alert("Saved!");
                        } catch (err) {
                            console.error(err);
                            alert("Error saving summary and metadata.");
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

                    const handlePublish = async () => {
                        try {
                            await axios.post(
                                `/api/inbounds/${item.id}/publish`,
                                {
                                    title: postTitle,
                                    categories: selectedCategories,
                                    meta: {
                                        sentiment,
                                        market_mover: marketMover,
                                        sources: [
                                            {
                                                title: source,
                                                url,
                                                excerpt: summary,
                                            },
                                        ],
                                    },
                                }
                            );
                            alert("Published to WordPress!");
                        } catch (err) {
                            console.error(err);
                            alert("Error publishing to WordPress.");
                        }
                    };

                    return (
                        <div
                            key={item.id}
                            className="flex flex-col p-6 border border-gray-200 rounded-lg shadow-sm bg-white dark:bg-gray-800 hover:shadow-md transition-shadow w-full gap-4"
                        >
                            {/* Header Section */}
                            <div className="flex items-center justify-between border-b border-gray-200 pb-4">
                                <div className="flex-grow">
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                        {isEditingTitle ? (
                                            <div className="flex items-center gap-2">
                                                <input
                                                    type="text"
                                                    value={postTitle}
                                                    onChange={(e) =>
                                                        setPostTitle(
                                                            e.target.value
                                                        )
                                                    }
                                                    className="w-full px-3 py-2 border rounded-md text-sm dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Enter post title"
                                                />
                                                <button
                                                    onClick={async () => {
                                                        try {
                                                            await axios.put(
                                                                `api/inbounds/${item.id}`,
                                                                {
                                                                    post_title:
                                                                        postTitle,
                                                                }
                                                            );
                                                            setIsEditingTitle(
                                                                false
                                                            );
                                                        } catch (err) {
                                                            console.error(err);
                                                            alert(
                                                                "Error saving title."
                                                            );
                                                        }
                                                    }}
                                                    className="p-2 text-green-600 hover:text-green-800 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                                                    title="Save Title"
                                                >
                                                    ✔
                                                </button>
                                            </div>
                                        ) : (
                                            <div className="flex items-center gap-2">
                                                <span>
                                                    {postTitle ||
                                                        "No title set"}
                                                </span>
                                                <button
                                                    onClick={() =>
                                                        setIsEditingTitle(true)
                                                    }
                                                    className="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100 transition-colors"
                                                    title="Edit Title"
                                                >
                                                    <svg
                                                        className="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        )}
                                    </h3>
                                    <div className="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <span className="font-medium mr-2">
                                            Source:
                                        </span>
                                        {isEditingSource ? (
                                            <div className="flex items-center gap-2 flex-grow">
                                                <input
                                                    type="text"
                                                    value={url}
                                                    onChange={(e) =>
                                                        setUrl(e.target.value)
                                                    }
                                                    className="flex-grow px-3 py-1 border rounded-md text-sm dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                />
                                                <button
                                                    onClick={handleSourceUpdate}
                                                    className="p-1 text-green-600 hover:text-green-800 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                                                    title="Save"
                                                >
                                                    ✔
                                                </button>
                                            </div>
                                        ) : (
                                            <div className="flex items-center gap-2">
                                                <a
                                                    href={item.url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="text-blue-500 hover:text-blue-600 hover:underline"
                                                >
                                                    {item.source}
                                                </a>
                                                <button
                                                    onClick={() =>
                                                        setIsEditingSource(true)
                                                    }
                                                    className="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100 transition-colors"
                                                    title="Edit Source"
                                                >
                                                    <svg
                                                        className="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Content Section */}
                            <div className="space-y-4">
                                {/* Categories */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Categories
                                    </label>
                                    <div className="flex flex-wrap gap-2">
                                        {[
                                            { id: 27, name: "GreenPill" },
                                            { id: 28, name: "Reports" },
                                            { id: 29, name: "Technology" },
                                            { id: 30, name: "US" },
                                            { id: 31, name: "World" },
                                        ].map((cat) => (
                                            <label
                                                key={cat.id}
                                                className="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={selectedCategories.includes(
                                                        cat.id
                                                    )}
                                                    onChange={() =>
                                                        toggleCategory(cat.id)
                                                    }
                                                    className="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                                />
                                                <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                    {cat.name}
                                                </span>
                                            </label>
                                        ))}
                                    </div>
                                </div>

                                {/* Summary */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Summary
                                    </label>
                                    <textarea
                                        value={summary}
                                        onChange={(e) =>
                                            setSummary(e.target.value)
                                        }
                                        className="w-full px-3 py-2 border rounded-md text-sm text-gray-700 dark:text-gray-200 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                        rows={8}
                                    />
                                </div>

                                {/* Metadata */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Sentiment
                                        </label>
                                        <select
                                            value={sentiment}
                                            onChange={(e) =>
                                                setSentiment(e.target.value)
                                            }
                                            className="w-full px-3 py-2 border rounded-md text-sm dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option value="neutral">
                                                Neutral
                                            </option>
                                            <option value="bullish">
                                                Bullish
                                            </option>
                                            <option value="bearish">
                                                Bearish
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Market Mover
                                        </label>
                                        <select
                                            value={marketMover}
                                            onChange={(e) =>
                                                setMarketMover(e.target.value)
                                            }
                                            className="w-full px-3 py-2 border rounded-md text-sm dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex justify-end items-center gap-3 pt-4 border-t border-gray-200">
                                <button
                                    type="button"
                                    onClick={handleRegenerate}
                                    title="Regenerate Summary"
                                    className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 transition-colors"
                                    disabled={isRegenerating}
                                >
                                    {isRegenerating ? (
                                        <svg
                                            className="animate-spin h-4 w-4"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                className="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                strokeWidth="4"
                                                fill="none"
                                            />
                                            <path
                                                className="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            />
                                        </svg>
                                    ) : (
                                        "Regenerate"
                                    )}
                                </button>
                                <button
                                    type="button"
                                    onClick={handleDelete}
                                    title="Delete"
                                    className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                >
                                    Delete
                                </button>
                                <button
                                    type="button"
                                    onClick={handleSave}
                                    className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                >
                                    Save
                                </button>
                                <button
                                    type="button"
                                    onClick={handlePublish}
                                    className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors"
                                >
                                    Publish
                                </button>
                            </div>
                        </div>
                    );
                })}
        </>
    );
}
