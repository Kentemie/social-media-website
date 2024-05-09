export const isImage = (attachment) => {
    return (attachment.mime || attachment.type).split("/")[0].toLowerCase() === "image";
};

export const readFile = async (file) => {
    return new Promise((resolve, reject) => {
        if (isImage(file)) {
            const reader = new FileReader();
            reader.onload = () => {
                resolve(reader.result);
            };
            reader.onerror = reject;

            reader.readAsDataURL((file));
        } else {
            resolve(null);
        }
    });

};
