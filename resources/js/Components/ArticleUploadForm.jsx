import { useState } from "react";
import axios from "axios";

export default function ArticleUploadForm({ auth }) {
    const [formData, setFormData] = useState({
        url: "",
        notes: "",
        pdf: null,
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [message, setMessage] = useState({ type: "", text: "" });

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleFileChange = (e) => {
        setFormData((prev) => ({
            ...prev,
            pdf: e.target.files[0],
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        setMessage({ type: "", text: "" });

        const submitData = new FormData();
        submitData.append("url", formData.url);
        submitData.append("notes", formData.notes);
        submitData.append("user_id", auth.user.id);
        if (formData.pdf) {
            submitData.append("pdf", formData.pdf);
        }

        try {
            const response = await axios.post("/api/inbounds", submitData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });

            setMessage({
                type: "success",
                text: "Article uploaded successfully!",
            });

            // Clear form
            setFormData({
                url: "",
                notes: "",
                pdf: null,
            });

            // Reset file input
            const fileInput = document.querySelector('input[type="file"]');
            if (fileInput) fileInput.value = "";
        } catch (error) {
            setMessage({
                type: "error",
                text: error.response?.data?.error || "Failed to upload article",
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6">
                {message.text && (
                    <div
                        className={`mb-6 p-4 rounded ${
                            message.type === "success"
                                ? "bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300"
                                : "bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300"
                        }`}
                    >
                        {message.text}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label
                            htmlFor="url"
                            className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            URL
                        </label>
                        <input
                            type="url"
                            id="url"
                            name="url"
                            value={formData.url}
                            onChange={handleInputChange}
                            className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                                     shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                     dark:bg-gray-700 dark:text-gray-300"
                            placeholder="https://example.com/article"
                        />
                    </div>

                    <div>
                        <label
                            htmlFor="notes"
                            className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Notes
                        </label>
                        <textarea
                            id="notes"
                            name="notes"
                            value={formData.notes}
                            onChange={handleInputChange}
                            rows="3"
                            className="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                                     shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                                     dark:bg-gray-700 dark:text-gray-300"
                            placeholder="Add any notes about the article..."
                        />
                    </div>

                    <div>
                        <label
                            htmlFor="pdf"
                            className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            PDF File
                        </label>
                        <input
                            type="file"
                            id="pdf"
                            name="pdf"
                            accept=".pdf"
                            onChange={handleFileChange}
                            className="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 dark:file:bg-indigo-900
                                file:text-indigo-700 dark:file:text-indigo-300
                                hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800"
                        />
                    </div>

                    <div className="flex items-center justify-end">
                        <button
                            type="submit"
                            disabled={isSubmitting}
                            className={`inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500
                                border border-transparent rounded-md font-semibold text-xs text-white
                                uppercase tracking-widest hover:bg-indigo-700 dark:hover:bg-indigo-400
                                focus:bg-indigo-700 dark:focus:bg-indigo-400 active:bg-indigo-900
                                dark:active:bg-indigo-300 focus:outline-none focus:ring-2
                                focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800
                                transition ease-in-out duration-150 ${
                                    isSubmitting
                                        ? "opacity-75 cursor-not-allowed"
                                        : ""
                                }`}
                        >
                            {isSubmitting ? "Uploading..." : "Upload Article"}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
