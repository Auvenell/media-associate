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
    const [uploadProgress, setUploadProgress] = useState(0);
    const [processingState, setProcessingState] = useState("");

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

    const getProgressBarColor = () => {
        if (processingState === "error") return "bg-red-500";
        if (uploadProgress === 100 && processingState === "processing")
            return "bg-yellow-500";
        if (uploadProgress === 100 && processingState === "complete")
            return "bg-green-500";
        return "bg-indigo-500";
    };

    const getProgressText = () => {
        if (processingState === "error") return "Error occurred";
        if (uploadProgress < 100) return `Uploading: ${uploadProgress}%`;
        if (processingState === "processing") return "Processing article...";
        if (processingState === "complete") return "Complete!";
        return "";
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        setMessage({ type: "", text: "" });
        setUploadProgress(0);
        setProcessingState("");

        const submitData = new FormData();
        submitData.append("url", formData.url);
        submitData.append("notes", formData.notes);
        submitData.append("user_id", auth.user.id);
        if (formData.pdf) {
            submitData.append("pdf", formData.pdf);
        }

        try {
            setProcessingState("uploading");
            const response = await axios.post("/api/inbounds", submitData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
                onUploadProgress: (progressEvent) => {
                    const progress = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    setUploadProgress(progress);
                    if (progress === 100) {
                        setProcessingState("processing");
                    }
                },
            });

            setProcessingState("complete");
            setMessage({
                type: "success",
                text: "Article uploaded and processed successfully!",
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
            setProcessingState("error");
            setMessage({
                type: "error",
                text: error.response?.data?.error || "Failed to upload article",
            });
        } finally {
            setTimeout(() => {
                setIsSubmitting(false);
                setUploadProgress(0);
                setProcessingState("");
            }, 3000);
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

                {(isSubmitting || processingState) && (
                    <div className="mb-6">
                        <div className="relative pt-1">
                            <div className="flex mb-2 items-center justify-between">
                                <div>
                                    <span className="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 dark:text-indigo-300">
                                        {getProgressText()}
                                    </span>
                                </div>
                            </div>
                            <div className="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200 dark:bg-indigo-900">
                                <div
                                    style={{
                                        width: `${uploadProgress}%`,
                                        transition: "width 0.5s ease-in-out",
                                    }}
                                    className={`shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center ${getProgressBarColor()}`}
                                ></div>
                            </div>
                        </div>
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
                            {isSubmitting ? "Processing..." : "Upload Article"}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
