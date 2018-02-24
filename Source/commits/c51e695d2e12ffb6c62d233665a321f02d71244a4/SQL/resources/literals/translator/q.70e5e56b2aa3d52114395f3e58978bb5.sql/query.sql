-- Get old value, if any
SELECT TR_literalTranslationVote.vote INTO @old_vote
FROM TR_literalTranslationVote
WHERE TR_literalTranslationVote.translation_id = $translation_id AND TR_literalTranslationVote.translator_id = $translator_id;

-- If no old value, set to 0
SET @old_vote = IFNULL(@old_vote, 0);
-- Map boolean to actual value for new vote
SET @new_vote = IF ($vote, 1, -1);

-- Add Vote	
INSERT INTO TR_literalTranslationVote (translation_id, translator_id, vote)
VALUES ($translation_id, $translator_id, @new_vote)
	ON DUPLICATE KEY UPDATE vote = @new_vote;
	
-- Update Translation Skor
UPDATE TR_literalTranslation
SET skor = skor - @old_vote + @new_vote
WHERE TR_literalTranslation.id = $translation_id;