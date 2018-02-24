-- Get old value
SELECT TR_literalTranslationVote.vote INTO @old_vote
FROM TR_literalTranslationVote
WHERE TR_literalTranslationVote.translation_id = $translation_id AND TR_literalTranslationVote.translator_id = $translator_id;

-- If no old value, set to 0
SET @old_vote = IFNULL(@old_vote, 0);

-- Remove Vote
DELETE FROM TR_literalTranslationVote
WHERE TR_literalTranslationVote.translation_id = $translation_id AND TR_literalTranslationVote.translator_id = $translator_id;

-- Change Translation Score
UPDATE TR_literalTranslation
SET score = score - @old_vote
WHERE TR_literalTranslation.id = $translation_id;